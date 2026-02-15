<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        // Clean custom logs at 12:00 AM (before backup)
        $schedule->command('logs:clean-requests --days=10')
            ->dailyAt('12:00')
            ->onSuccess(fn() => \Log::info('Scheduler working :: logs:clean-requests --days=10'));

        // Daily DB backup at 01:10 AM
        $schedule->command('backup:run --only-db --disable-notifications')
            ->dailyAt('12:05')
            ->onSuccess(fn() => \Log::info('Scheduler working :: backup:run --only-db --disable-notifications'));

        // Backup cleanup at 01:20 AM
        $schedule->command('backup:clean --disable-notifications')
            ->dailyAt('12:10')
            ->onSuccess(fn() => \Log::info('Scheduler working :: backup:clean --disable-notifications'));


        // Clean activity logs at 01:30 AM (after backup)
        $schedule->command('activitylog:clean --days=10 --force')
            ->dailyAt('12:15')
            ->onSuccess(fn() => \Log::info('Scheduler working :: activitylog:clean --days=10 --force'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Automatically load all Artisan commands in app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Optionally load specific command files
        // require base_path('routes/console.php');
    }
}
