<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use ZipArchive;

class BackupController extends Controller
{
    private function backupPath()
    {
        return storage_path('app/backups');
    }

    private function runMysqldump($filePath)
    {
        $db = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        $host = env('DB_HOST', '127.0.0.1');

        $command = "mysqldump -h {$host} -u {$user} --password=\"{$pass}\" {$db} > {$filePath}";
        exec($command);
    }

    private function zipWithPassword($sourceSql, $zipPath)
    {
        $zip = new ZipArchive;
        $pwd = env('BACKUP_DOWNLOAD_PASSWORD');

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {

            $zip->addFile($sourceSql, basename($sourceSql));
            $zip->setEncryptionName(basename($sourceSql), ZipArchive::EM_AES_256, $pwd);

            $zip->close();
        }
    }

    // ------------------------------------------------------------
    //  DISPLAY MANAGEMENT PAGE
    // ------------------------------------------------------------
    public function showManageView()
    {
        $backups = Backup::orderByDesc('created_at')->get();

        return view('common.backup-manage', ['backups' => $backups]);
    }

    // ------------------------------------------------------------
    //  MANUAL BACKUP + DOWNLOAD
    // ------------------------------------------------------------
    public function exportDatabase(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        if (!is_dir($this->backupPath())) {
            mkdir($this->backupPath(), 0777, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFile = "{$this->backupPath()}/backup-{$timestamp}.sql";
        $zipFile = "{$this->backupPath()}/backup-{$timestamp}.zip";

        $this->runMysqldump($sqlFile);
        $this->zipWithPassword($sqlFile, $zipFile);

        unlink($sqlFile); // remove raw SQL file

        $backup = Backup::create([
            'name' => basename($zipFile),
            'type' => 'Manual',
        ]);

        AuditLogger::log(
            'backup_export',
            'Backup',
            $backup->id,
            [],
            ['message' => 'Database backup created and downloaded'],
            'backup'
        );

        return back()->with('success', 'Backup created successfully.');
    }

    // ------------------------------------------------------------
    //  DELETE BACKUP
    // ------------------------------------------------------------
    public function deleteBackup(Request $request, $fileName)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        if (!Hash::check($request->password, Auth::user()->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        $path = $this->backupPath() . '/' . $fileName;

        if (file_exists($path)) {
            unlink($path);
        }

        $backup = Backup::where('name', $fileName)->first();

        if ($backup) {
            $backup->delete();
        }

        AuditLogger::log(
            'backup_delete',
            'Backup',
            $backup?->id,
            [],
            ['message' => "Backup {$fileName} deleted"],
            'backup'
        );

        return back()->with('success', 'Backup deleted.');
    }

    public function download($id)
    {
        $backup = Backup::findOrFail($id);
        $filePath = $this->backupPath() . '/' . $backup->name;

        if (!file_exists($filePath)) {
            return back()->with('error', 'Backup file not found.');
        }

        return response()->download($filePath);
    }

    // ------------------------------------------------------------
    //  AUTOMATIC BACKUP (for scheduler)
    // ------------------------------------------------------------
    public function autoBackup()
    {
        if (!is_dir($this->backupPath())) {
            mkdir($this->backupPath(), 0777, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $sqlFile = "{$this->backupPath()}/auto-{$timestamp}.sql";
        $zipFile = "{$this->backupPath()}/auto-{$timestamp}.zip";

        $this->runMysqldump($sqlFile);
        $this->zipWithPassword($sqlFile, $zipFile);

        unlink($sqlFile);

        // cleanup
        $keep = env('BACKUP_RETENTION_DAYS', 7);
        foreach (glob($this->backupPath() . '/*.zip') as $file) {
            if (filemtime($file) < now()->subDays($keep)->timestamp) {
                unlink($file);
            }
        }

        $backup = Backup::create([
            'name' => basename($zipFile),
            'type' => 'Auto',
        ]);

        AuditLogger::log(
            'backup_export',
            'Backup',
            $backup->id,
            [],
            ['message' => 'Automatic backup created'],
            'backup'
        );
    }
}
