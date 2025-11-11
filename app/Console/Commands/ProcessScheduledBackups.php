<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\DatabaseBackupController;
use Carbon\Carbon;

class ProcessScheduledBackups extends Command
{
    protected $signature = 'backup:process-scheduled';
    protected $description = 'Process scheduled database backups';

    public function handle()
    {
        $backupController = new DatabaseBackupController();
        $scheduleFile = storage_path('app/backups/scheduled_backups.json');

        if (!file_exists($scheduleFile)) {
            $this->info('No scheduled backups found.');
            return;
        }

        $schedules = json_decode(file_get_contents($scheduleFile), true) ?? [];
        $processed = [];
        $remaining = [];

        foreach ($schedules as $schedule) {
            $shouldProcess = $this->shouldProcessSchedule($schedule);

            if ($shouldProcess) {
                try {
                    $backupController->generateBackup($schedule['backup_type']);
                    $this->info("Backup created for type: {$schedule['backup_type']}");
                } catch (\Exception $e) {
                    $this->error("Failed to create backup: " . $e->getMessage());
                }
            } else {
                $remaining[] = $schedule;
            }
        }

        // Update schedule file with remaining schedules
        file_put_contents($scheduleFile, json_encode($remaining, JSON_PRETTY_PRINT));
    }

    private function shouldProcessSchedule($schedule)
    {
        $scheduledAt = Carbon::parse($schedule['scheduled_at']);
        $now = Carbon::now();

        switch ($schedule['schedule_type']) {
            case 'monthly':
                return $now->isLastOfMonth() && $now->hour === 0 && $now->minute === 0;
            
            case 'weekly':
                return $now->isSunday() && $now->hour === 0 && $now->minute === 0;
            
            case 'daily':
                return $now->hour === 0 && $now->minute === 0;
            
            default:
                return false;
        }
    }
}