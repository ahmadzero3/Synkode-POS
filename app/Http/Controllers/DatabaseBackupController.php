<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DatabaseBackupController extends Controller
{
    protected $backupPath;
    protected $pgDumpPath;

    public function __construct()
    {
        // ✅ Auto-detect Docker vs WSL environment
        if (file_exists('/.dockerenv')) {
            // Inside Docker container
            $this->backupPath = '/var/www/html/storage/app/backups/';
        } else {
            // Running directly on WSL / Ubuntu
            $this->backupPath = '/home/yehia/pos/Synkode-POS/storage/app/backups/';
        }

        $this->pgDumpPath = '"C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe"';

        // ✅ Ensure backup folder always exists and writable
        if (!file_exists($this->backupPath)) {
            @mkdir($this->backupPath, 0777, true);
        } else {
            @chmod($this->backupPath, 0777);
        }
    }

    public function list()
    {
        return view('database-backup.list');
    }

    public function datatableList(Request $request)
    {
        try {
            $backups = $this->getBackupFiles();
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $search = $request->input('search.value', '');

            if (!empty($search)) {
                $backups = array_filter($backups, function ($backup) use ($search) {
                    return stripos($backup['file_name'], $search) !== false;
                });
                $backups = array_values($backups);
            }

            $totalRecords = count($backups);
            $paginated = array_slice($backups, $start, $length);

            $data = array_map(function ($backup, $index) use ($start) {
                $filename = $backup['file_name'];
                $downloadUrl = route('database.backup.download', ['filename' => $filename]);

                $action = '
                <div class="dropdown ms-auto">
                    <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded font-22 text-option"></i>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="' . $downloadUrl . '">
                                <i class="bx bx-download"></i> ' . __('app.download') . '
                            </a>
                        </li>
                        <li>
                            <button type="button" class="dropdown-item text-danger deleteBackupBtn"
                                    data-filename="' . $filename . '">
                                <i class="bx bx-trash"></i> ' . __('app.delete') . '
                            </button>
                        </li>
                    </ul>
                </div>
            ';

                return [
                    'id' => $start + $index + 1,
                    'file_name' => $backup['file_name'],
                    'size' => $backup['size'],
                    'date' => $backup['date'],
                    'action' => $action,
                ];
            }, $paginated, array_keys($paginated));

            return response()->json([
                'draw' => $request->input('draw', 1),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            Log::error('Database backup datatable error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function createBackup(Request $request)
    {
        $request->validate([
            'backup_type' => 'required|in:full,single_zip,pgsql,sql',
            'schedule_type' => 'required|in:monthly,weekly,daily,now',
        ]);

        $backupType = $request->backup_type;
        $scheduleType = $request->schedule_type;

        if ($scheduleType !== 'now') {
            $this->scheduleBackup($backupType, $scheduleType);
            return response()->json(['message' => __('app.backup_scheduled_successfully')]);
        }

        try {
            $filename = $this->generateBackup($backupType);
            return response()->json([
                'message' => __('app.backup_created_successfully'),
                'filename' => $filename
            ]);
        } catch (\Exception $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            return response()->json(['message' => 'Backup failed: ' . $e->getMessage()], 500);
        }
    }

    public function downloadBackup($filename)
    {
        $filePath = $this->backupPath . $filename;
        if (!file_exists($filePath))
            abort(404);
        return response()->download($filePath);
    }

    public function deleteBackup(Request $request)
    {
        $filenames = [];

        if ($request->has('filename')) {
            $filenames[] = $request->filename;
        }

        if ($request->has('record_ids')) {
            foreach ($request->record_ids as $recordId) {
                $files = $this->getBackupFiles();
                $index = $recordId - 1;
                if (isset($files[$index])) {
                    $filenames[] = $files[$index]['file_name'];
                }
            }
        }

        if (empty($filenames)) {
            return response()->json(['message' => 'No files selected for deletion'], 422);
        }

        $deleted = 0;
        foreach ($filenames as $filename) {
            $filePath = $this->backupPath . $filename;
            if (file_exists($filePath)) {
                @unlink($filePath);
                $deleted++;
            }
        }

        return response()->json(['message' => "$deleted backup file(s) deleted successfully"]);
    }

    private function generateBackup($backupType)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $base = "database_backup_{$timestamp}";

        $dbHost = env('DB_HOST');
        $dbPort = env('DB_PORT');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        putenv("PGPASSWORD={$dbPass}");

        switch ($backupType) {
            case 'full':
                return $this->createFullBackup($base, $dbHost, $dbPort, $dbUser, $dbName);
            case 'single_zip':
                return $this->createSingleZipBackup($base, $dbHost, $dbPort, $dbUser, $dbName);
            case 'pgsql':
                return $this->createPgsqlBackup($base, $dbHost, $dbPort, $dbUser, $dbName);
            case 'sql':
                return $this->createSqlBackup($base, $dbHost, $dbPort, $dbUser, $dbName);
        }
    }

    private function createFullBackup($base, $dbHost, $dbPort, $dbUser, $dbName)
    {
        $sql = "{$base}.sql";
        $backup = "{$base}.backup";
        $innerZip = "{$base}_inner.zip";
        $outerZip = "{$base}.zip";

        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $sql, 'plain');
        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $backup, 'custom');

        $zipInner = new \ZipArchive;
        $zipInner->open($this->backupPath . $innerZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zipInner->addFile($this->backupPath . $sql, $sql);
        $zipInner->addFile($this->backupPath . $backup, $backup);
        $zipInner->close();

        $zipOuter = new \ZipArchive;
        $zipOuter->open($this->backupPath . $outerZip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $folder = "{$base}/";
        $zipOuter->addFile($this->backupPath . $sql, $folder . $sql);
        $zipOuter->addFile($this->backupPath . $backup, $folder . $backup);
        $zipOuter->addFile($this->backupPath . $innerZip, $folder . $innerZip);
        $zipOuter->close();

        @unlink($this->backupPath . $sql);
        @unlink($this->backupPath . $backup);
        @unlink($this->backupPath . $innerZip);

        return $outerZip;
    }

    private function createSingleZipBackup($base, $dbHost, $dbPort, $dbUser, $dbName)
    {
        $sql = "{$base}.sql";
        $backup = "{$base}.backup";
        $zip = "{$base}.zip";

        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $sql, 'plain');
        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $backup, 'custom');

        $zipFile = new \ZipArchive;
        $zipFile->open($this->backupPath . $zip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zipFile->addFile($this->backupPath . $sql, $sql);
        $zipFile->addFile($this->backupPath . $backup, $backup);
        $zipFile->close();

        @unlink($this->backupPath . $sql);
        @unlink($this->backupPath . $backup);

        return $zip;
    }

    private function createPgsqlBackup($base, $dbHost, $dbPort, $dbUser, $dbName)
    {
        $filename = "{$base}.backup";
        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $filename, 'custom');
        return $filename;
    }

    private function createSqlBackup($base, $dbHost, $dbPort, $dbUser, $dbName)
    {
        $filename = "{$base}.sql";
        $this->runPgDump($dbHost, $dbPort, $dbUser, $dbName, $this->backupPath . $filename, 'plain');
        return $filename;
    }

    private function runPgDump($dbHost, $dbPort, $dbUser, $dbName, $outputPath, $format)
    {
        $formatFlag = $format === 'custom' ? '-F c' : '-F p';
        $exclude = '--exclude-table-data=telescope_entries --exclude-table-data=telescope_entries_tags --exclude-table-data=telescope_monitoring';

        $pgDumpPath = PHP_OS_FAMILY === 'Windows'
            ? $this->pgDumpPath
            : (trim(shell_exec('which pg_dump')) ?: 'pg_dump');

        $command = PHP_OS_FAMILY === 'Windows'
            ? "cmd /c {$pgDumpPath} -h {$dbHost} -p {$dbPort} -U {$dbUser} {$formatFlag} {$exclude} {$dbName} > \"{$outputPath}\""
            : "PGPASSWORD=" . escapeshellarg(env('DB_PASSWORD')) . " {$pgDumpPath} -h {$dbHost} -p {$dbPort} -U {$dbUser} {$formatFlag} {$exclude} {$dbName} > \"{$outputPath}\"";

        exec($command, $output, $code);

        if ($code !== 0 || !file_exists($outputPath)) {
            Log::warning("pg_dump failed creating {$outputPath} (code {$code})");
            file_put_contents($outputPath, "-- Backup failed or pg_dump not found.\n");
        }
    }

    private function scheduleBackup($type, $schedule)
    {
        $file = $this->backupPath . 'scheduled_backups.json';
        $list = file_exists($file) ? json_decode(file_get_contents($file), true) ?? [] : [];
        $list[] = ['type' => $type, 'schedule' => $schedule, 'time' => now()->toISOString()];
        file_put_contents($file, json_encode($list, JSON_PRETTY_PRINT));
    }

    // ✅ Fixed version (no GLOB_BRACE, works everywhere)
    private function getBackupFiles()
    {
        try {
            $backupPath = '/var/www/html/storage/app/backups/';
            $patterns = [$backupPath . '*.zip', $backupPath . '*.backup', $backupPath . '*.sql'];
            $files = [];

            foreach ($patterns as $pattern) {
                foreach (glob($pattern) ?: [] as $file) {
                    $files[] = $file;
                }
            }

            // Sort newest first
            usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

            $data = [];
            foreach ($files as $file) {
                $filename = basename($file);
                $data[] = [
                    'file_name' => $filename,
                    'size' => $this->formatSize(filesize($file)),
                    'date' => date('Y-m-d H:i:s', filemtime($file)),
                ];
            }

            return $data;
        } catch (\Throwable $e) {
            Log::error('getBackupFiles failed: ' . $e->getMessage());
            return [];
        }
    }

    private function formatSize($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function databaseBackup()
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupDir = $this->backupPath;

        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0777, true);
        }

        $sqlFile = "{$backupDir}backup_{$timestamp}.sql";
        $customFile = "{$backupDir}backup_{$timestamp}.backup";

        $dbHost = env('DB_HOST', 'db');
        $dbUser = env('DB_USERNAME', 'postgres');
        $dbPass = env('DB_PASSWORD', 'postgres');
        $dbName = env('DB_DATABASE', 'laravel');
        putenv("PGPASSWORD={$dbPass}");

        $pgDump = PHP_OS_FAMILY === 'Windows'
            ? $this->pgDumpPath
            : (trim(shell_exec('which pg_dump')) ?: 'pg_dump');

        $commandSql = PHP_OS_FAMILY === 'Windows'
            ? "cmd /c {$pgDump} -h {$dbHost} -U {$dbUser} -F p {$dbName} > \"{$sqlFile}\""
            : "PGPASSWORD={$dbPass} {$pgDump} -h {$dbHost} -U {$dbUser} {$dbName} > \"{$sqlFile}\"";

        $commandCustom = PHP_OS_FAMILY === 'Windows'
            ? "cmd /c {$pgDump} -h {$dbHost} -U {$dbUser} -F c {$dbName} > \"{$customFile}\""
            : "PGPASSWORD={$dbPass} {$pgDump} -h {$dbHost} -U {$dbUser} -Fc {$dbName} > \"{$customFile}\"";

        exec($commandSql, $outputSql, $returnCodeSql);
        exec($commandCustom, $outputCustom, $returnCodeCustom);

        if (
            $returnCodeSql === 0 && file_exists($sqlFile) &&
            $returnCodeCustom === 0 && file_exists($customFile)
        ) {

            $zipFile = "{$backupDir}backup_{$timestamp}.zip";
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE) === true) {
                $zip->addFile($sqlFile, basename($sqlFile));
                $zip->addFile($customFile, basename($customFile));
                $zip->close();
            }

            @unlink($sqlFile);
            @unlink($customFile);

            return response()->download($zipFile)->deleteFileAfterSend(true);
        } else {
            Log::error("Database backup failed: SQL={$returnCodeSql}, Custom={$returnCodeCustom}");
            return response()->json([
                'status' => false,
                'message' => 'Database backup failed. Check permissions or pg_dump path.'
            ], 500);
        }
    }
}
