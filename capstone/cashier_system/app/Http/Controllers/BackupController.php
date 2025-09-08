<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use ZipArchive;

class BackupController extends Controller
{
 
    public function showManageView() {
        $backups = Backup::orderByDesc('created_at')->get();
        return view('common.backup-manage', compact('backups'));
    }

    // Save a backup to DB instead of download
    public function exportDatabase(Request $request) {
        // ✅ Step 1: Require password confirmation
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Invalid password.'], 'exportErrorBag');
        }

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
                continue; // skip sensitive/system tables
            }

            if ($tableType === 'VIEW') {
                $sqlDump .= "DROP VIEW IF EXISTS `{$tableName}`;\n";
                $createViewResult = DB::select("SHOW CREATE VIEW `{$tableName}`")[0];
                $createView = array_values((array)$createViewResult)[1];
                $sqlDump .= $createView . ";\n\n";
            } else {
                $sqlDump .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createTable = array_values((array)$createTableResult)[1];
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

        // ✅ Save backup in DB
        $backup = Backup::create([
            'name' => "Backup_{$date}_#{$countToday}",
            'sql_content' => encrypt($sqlDump),
            'type' => 'Manual',
        ]);

        // Cleanup old backups
        $daysToKeep = config('backup.retention_days');
        Backup::where('created_at', '<', now()->subDays($daysToKeep))->delete();

        AuditLogger::log(
            event: 'backup_export',
            auditableType: 'Backup',
            auditableId: $backup->id,
            oldValues: [],
            newValues: [
                    'message' => 'Database backup created & downloaded',
                    'timestamp' => now()->toDateTimeString(),
                    'type' => 'Manual'
                ],
            tags: 'backup'
        );

        // ✅ Step 2: Create temporary password-protected ZIP with SQL
        $filename = $backup->name . '.sql';
        $zipFilename = $backup->name . '.zip';
        $tempPath = storage_path("app/temp/{$zipFilename}");

        if (!is_dir(storage_path("app/temp"))) {
            mkdir(storage_path("app/temp"), 0777, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $zipPassword = env('BACKUP_DOWNLOAD_PASSWORD');

            // Add file and encrypt it
            $zip->addFromString($filename, $sqlDump);
            $zip->setEncryptionName($filename, ZipArchive::EM_AES_256, $zipPassword);

            $zip->close();
        } else {
            return back()->withErrors(['zip' => 'Failed to create ZIP archive.'], 'exportErrorBag');
        }

        // ✅ Step 3: Return ZIP download and delete after
        return response()->download($tempPath, $zipFilename)->deleteFileAfterSend(true);
    }

    // Save a backup automatically (for scheduler)
    public function autoBackup() {
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
                continue; // skip audit logs & backups table
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

        $backup = Backup::create([
            'name' => "AutoBackup_{$date}_#{$countToday}",
            'sql_content' => encrypt($sqlDump),
            'type' => 'Automatic',
        ]);

        // Apply retention policy
        $daysToKeep = config('backup.retention_days', 7);
        Backup::where('created_at', '<', now()->subDays($daysToKeep))->delete();

        AuditLogger::log(
            event: 'backup_export',
            auditableType: 'Backup',
            auditableId: $backup->id,
            oldValues: [],
            newValues: [
                'message' => 'Database backup created automatically',
                'timestamp' => now()->toDateTimeString(),
                'type' => 'Automatic'
            ],
            tags: 'backup'
        );
    }

    // Restore backup by ID
    public function restoreBackup(Request $request, $id) {

        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

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

    public function deleteBackup(Request $request, $id) {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

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
