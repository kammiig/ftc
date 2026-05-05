<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use ZipArchive;

class BackupService
{
    public function create(string $type, ?User $user = null): Backup
    {
        if (! in_array($type, Backup::TYPES, true)) {
            throw new RuntimeException('Invalid backup type selected.');
        }

        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);

        $timestamp = now()->format('Ymd_His');
        $filename = "ftc_{$type}_backup_{$timestamp}.zip";
        $path = $directory.'/'.$filename;

        $backup = Backup::query()->create([
            'filename' => $filename,
            'path' => 'backups/'.$filename,
            'type' => $type,
            'status' => 'running',
            'created_by' => $user?->id,
        ]);

        try {
            if (! class_exists(ZipArchive::class)) {
                throw new RuntimeException('PHP ZipArchive extension is not enabled on this server.');
            }

            $zip = new ZipArchive();

            if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Unable to create backup ZIP file.');
            }

            $zip->addFromString('database/ftc_database.sql', $this->databaseDump());

            if ($type === 'full') {
                $this->addDirectoryToZip($zip, storage_path('app/public'), 'uploads');
                $this->addDirectoryToZip($zip, storage_path('app/private/pdfs'), 'generated-pdfs');
                $this->addDirectoryToZip($zip, storage_path('app/generated-pdfs'), 'legacy-generated-pdfs');
                $this->addFileIfExists($zip, public_path('assets/images/ftc-logo.png'), 'public/assets/images/ftc-logo.png');
            }

            $zip->close();

            $backup->forceFill([
                'status' => 'completed',
                'size_bytes' => File::size($path),
                'completed_at' => now(),
                'message' => 'Backup created successfully.',
            ])->save();

            ActivityLog::record('backup_created', ucfirst($type).' backup created: '.$filename, $backup);

            return $backup->refresh();
        } catch (\Throwable $exception) {
            if (File::exists($path)) {
                File::delete($path);
            }

            $backup->forceFill([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'completed_at' => now(),
            ])->save();

            ActivityLog::record('backup_failed', ucfirst($type).' backup failed: '.$exception->getMessage(), $backup);

            throw $exception;
        }
    }

    public function absolutePath(Backup $backup): string
    {
        return storage_path('app/'.$backup->path);
    }

    private function databaseDump(): string
    {
        $pdo = DB::connection()->getPdo();
        $database = DB::connection()->getDatabaseName();
        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->values();

        $sql = "-- FTC Installment Management System database backup\n";
        $sql .= '-- Database: '.$database."\n";
        $sql .= '-- Generated: '.now()->toDateTimeString()."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $createRow = (array) DB::select("SHOW CREATE TABLE `{$table}`")[0];
            $createStatement = $createRow['Create Table'] ?? array_values($createRow)[1];

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStatement.";\n\n";

            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use (&$sql, $table, $pdo): void {
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $columns = collect(array_keys($data))
                        ->map(fn ($column) => '`'.str_replace('`', '``', $column).'`')
                        ->implode(', ');
                    $values = collect(array_values($data))
                        ->map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value))
                        ->implode(', ');

                    $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES ({$values});\n";
                }
            });

            $sql .= "\n";
        }

        return $sql."SET FOREIGN_KEY_CHECKS=1;\n";
    }

    private function addDirectoryToZip(ZipArchive $zip, string $directory, string $zipPrefix): void
    {
        if (! File::isDirectory($directory)) {
            return;
        }

        foreach (File::allFiles($directory) as $file) {
            if ($file->getFilename() === '.gitignore') {
                continue;
            }

            $relativePath = trim($zipPrefix.'/'.str_replace($directory, '', $file->getRealPath()), '/');
            $zip->addFile($file->getRealPath(), str_replace('\\', '/', $relativePath));
        }
    }

    private function addFileIfExists(ZipArchive $zip, string $path, string $zipPath): void
    {
        if (File::exists($path)) {
            $zip->addFile($path, $zipPath);
        }
    }
}
