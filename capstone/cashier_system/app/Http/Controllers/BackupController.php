<?php

namespace App\Http\Controllers;

use App\Models\Backup;
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
        $backups = Backup::orderByDesc('created_at')->get();
        return view('common.backup-manage', compact('backups'));
    }

    // Save a backup to DB instead of download
    public function exportDatabase() {
        $database = env('DB_DATABASE');
        $tables = DB::select("SHOW FULL TABLES WHERE Table_type IN ('BASE TABLE', 'VIEW')");
        $tableKey = "Tables_in_{$database}";
        $typeKey = "Table_type";

        $pdo = DB::getPdo();

        $sqlDump = "-- Database export for {$database}\n-- Generated at " . now() . "\n\n";
        $sqlDump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;
            $tableType = $table->$typeKey;

            if ($tableName === 'audits' || $tableName === 'backups') {
                continue; // don’t back up audit logs or backups table itself
            }

            if ($tableType === 'VIEW') {
                $sqlDump .= "DROP VIEW IF EXISTS `{$tableName}`;\n";
                $createViewResult = DB::select("SHOW CREATE VIEW `{$tableName}`")[0];
                $createViewArray = (array) $createViewResult;
                $createView = array_values($createViewArray)[1];
                $sqlDump .= $createView . ";\n\n";
            } else {
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createTableArray = (array) $createTableResult;
                $createTable = array_values($createTableArray)[1];
                $sqlDump .= $createTable . ";\n\n";

                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $columns = array_map(fn($col) => "`$col`", array_keys((array)$row));
                    $values = array_map(function ($value) use ($pdo) {
                        return is_null($value) ? "NULL" : $pdo->quote($value);
                    }, (array)$row);

                    $sqlDump .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlDump .= "\n";
            }
        }

        $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $date = now()->format('Y-m-d');
        $countToday = Backup::whereDate('created_at', now()->toDateString())->count() + 1;

        $last = Backup::orderByDesc('created_at')->first();
        if ($last && $last->created_at->gt(now()->subMinutes(10))) {
            return back()->with('error', 'Please wait before creating another backup.');
        }

        $backup = Backup::create([
            'name' => "Backup_{$date}_#{$countToday}",
            'sql_content' => encrypt($sqlDump),
        ]);

        // After creating a new backup
        $maxBackups = 5;
        $idsToKeep = Backup::orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($maxBackups)
            ->pluck('id');

        Backup::whereNotIn('id', $idsToKeep)->delete();

        AuditLogger::log(
            event: 'backup_export',
            auditableType: 'Backup',
            auditableId: $backup->id,
            oldValues: [],
            newValues: ['message' => 'Database backup saved', 'timestamp' => now()->toDateTimeString()],
            tags: 'backup'
        );

        return redirect()->route('backups.manage')->with('success', 'Backup created and saved!');
    }

    // Restore backup by ID
    public function restoreBackup($id) {
        $backup = Backup::findOrFail($id);
        $sql = decrypt($backup->sql_content);

        if ($backup->created_at->lt(now()->subDays(1))) {
            return redirect()->back()->with('error', 'This backup is too old to restore.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::unprepared($sql);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            AuditLogger::log(
                event: 'backup_restore',
                auditableType: 'Backup',
                auditableId: $backup->id,
                oldValues: [],
                newValues: ['message' => 'Database restored from backup', 'timestamp' => now()->toDateTimeString()],
                tags: 'backup'
            );

            return redirect()->route('backups.manage')->with('success', 'Database restored successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    public function deleteBackup($id)
    {
        $backup = Backup::findOrFail($id);

        try {
            $backup->delete();

            AuditLogger::log(
                event: 'backup_delete',
                auditableType: 'Backup',
                auditableId: $id,
                oldValues: ['name' => $backup->name, 'created_at' => $backup->created_at],
                newValues: ['message' => 'Backup deleted', 'timestamp' => now()->toDateTimeString()],
                tags: 'backup'
            );

            return redirect()->route('backups.manage')->with('success', 'Backup deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

}
