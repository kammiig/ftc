<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class CreateBackupCommand extends Command
{
    protected $signature = 'ftc:backup {type=full : database or full}';

    protected $description = 'Create an FTC database-only or full backup for cPanel cron jobs.';

    public function handle(BackupService $backupService): int
    {
        $backup = $backupService->create($this->argument('type'));

        $this->info('Backup created: '.$backup->filename);

        return self::SUCCESS;
    }
}
