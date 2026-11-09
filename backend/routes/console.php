<?php

use App\Services\Backup\DatabaseBackupService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('db:backup {--type=daily : daily, predeploy or manual}', function (DatabaseBackupService $backups) {
    try {
        $backup = $backups->create((string) $this->option('type'));
    } catch (\Throwable $e) {
        report($e);
        $this->error('Zaxira olinmadi: ' . $e->getMessage());

        return 1;
    }

    $this->info("Zaxira olindi: {$backup['name']} ({$backup['size']} bayt)");

    return 0;
})->purpose('Save a verified, compressed copy of the database');

// Automatically release unpaid inventory reservations every 5 minutes
Schedule::command('inventory:release-expired')->everyFiveMinutes();

// Nightly database copy, at the quietest hour
Schedule::command('db:backup --type=daily')->timezone('Asia/Tashkent')->dailyAt('03:00');

