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
        $schedule->command('logs:clean-requests --days=5')
//            ->dailyAt('1:00')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->onSuccess(fn() => \Log::info('Scheduler work Done :: logs:clean-requests --days=5'))
            ->onFailure(fn() => \Log::info('Scheduler work Fail :: logs:clean-requests --days=5'));

        /*// Daily DB backup at 01:10 AM
        $schedule->command('backup:run --only-db --disable-notifications')
            ->dailyAt('1:40')
            ->onSuccess(fn() => \Log::info('Scheduler working :: backup:run --only-db --disable-notifications'));

        // Backup cleanup at 01:20 AM
        $schedule->command('backup:clean --disable-notifications')
            ->dailyAt('2:40')
            ->onSuccess(fn() => \Log::info('Scheduler working :: backup:clean --disable-notifications'));


        // Clean activity logs at 01:30 AM (after backup)
        $schedule->command('activitylog:clean --days=5 --force')
            ->dailyAt('3:30')
            ->onSuccess(fn() => \Log::info('Scheduler working :: activitylog:clean --days=5 --force'));*/
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
