<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BackupController extends Controller
{
 
    public function showManageView() {
        return view('common.backup-manage');
    }

    // Export DB as SQL file without using mysqldump
    public function exportDatabase() {
        $database = env('DB_DATABASE');
        // Get both tables and views with their type
        $tables = DB::select("SHOW FULL TABLES WHERE Table_type IN ('BASE TABLE', 'VIEW')");
        $tableKey = "Tables_in_{$database}";
        $typeKey = "Table_type";

        $pdo = DB::getPdo();

        $sqlDump = "-- Database export for {$database}\n-- Generated at " . now() . "\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $tableType = $table->$typeKey; // 'BASE TABLE' or 'VIEW'

            // Skip the audits table to prevent overwriting audit logs on restore
            if ($tableName === 'audits') {
                continue;
            }

            if ($tableType === 'VIEW') {
                // Drop view statement
                $sqlDump .= "DROP VIEW IF EXISTS `{$tableName}`;\n";

                // Create view statement
                $createViewResult = DB::select("SHOW CREATE VIEW `{$tableName}`")[0];
                $createViewArray = (array) $createViewResult;
                // The create statement is usually in the second column (index 1)
                $createView = array_values($createViewArray)[1];

                $sqlDump .= $createView . ";\n\n";

                // Views do not contain data, so no inserts

            } else {
                // Drop table statement
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

                // Create table statement
                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createTableArray = (array) $createTableResult;
                $createTable = array_values($createTableArray)[1];

                $sqlDump .= $createTable . ";\n\n";

                // Insert data for tables only
                $rows = DB::table($tableName)->get();

                foreach ($rows as $row) {
                    $columns = array_map(fn($col) => "`$col`", array_keys((array)$row));

                    $values = array_map(function ($value) use ($pdo) {
                        if (is_null($value)) {
                            return "NULL";
                        }
                        return $pdo->quote($value); // Properly escapes and quotes the string
                    }, (array)$row);

                    $sqlDump .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }

                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = "backup_" . date('Ymd_His') . ".sql";

        // Audit log
        // After successful export (e.g., right before returning the response)
        AuditLogger::log(
            event: 'backup_export',
            auditableType: 'System',
            auditableId: 0,
            oldValues: [],
            newValues: ['message' => 'Database backup exported', 'timestamp' => now()->toDateTimeString()],
            tags: 'backup'
        );

        return Response::make($sqlDump, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    // Handle the uploaded SQL file and import it
    public function restoreDatabase(Request $request) {
        $validator = Validator::make($request->all(), [
            'sql_file' => 'required|file|mimes:sql,txt',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }

        $path = $request->file('sql_file')->getRealPath();
        $sql = File::get($path);

        try {
            // Optional but safe: disable FK checks before import
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Run entire SQL dump at once
            DB::unprepared($sql);

            // Re-enable FK checks after import
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // After successful export (e.g., right before returning the response)

            AuditLogger::log(
                event: 'backup_import',
                auditableType: 'System',
                auditableId: 0,
                oldValues: [],
                newValues: ['message' => 'Database backup imported', 'timestamp' => now()->toDateTimeString()],
                tags: 'backup'
            );

            return redirect()->route('backups.manage')->with('success', 'Database restored successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

}
