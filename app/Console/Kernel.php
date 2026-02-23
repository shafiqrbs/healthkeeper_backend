<?php

declare(strict_types=1);

namespace App\Console;

use App\Jobs\ProcessExpiredItems;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('logs:clean-requests --days=3')
            ->dailyAt('1:00')
            ->withoutOverlapping()
            ->onSuccess(fn() => \Log::info('Scheduler work Done :: logs:clean-requests --days=5'))
            ->onFailure(fn() => \Log::info('Scheduler work Fail :: logs:clean-requests --days=5'));

        $schedule->command('activitylog:clean --days=3 --force')
            ->dailyAt('1:30')
            ->withoutOverlapping()
            ->onSuccess(fn() => \Log::info('Scheduler working Done :: activitylog:clean --days=5 --force'))
            ->onFailure(fn() => \Log::info('Scheduler working Fail :: activitylog:clean --force'));

        // Process expire stock product
        $schedule->job(new ProcessExpiredItems())->dailyAt('2:10')->withoutOverlapping();

        // Daily DB backup at 01:10 AM
        $schedule->command('backup:run --only-db --disable-notifications')
            ->dailyAt('3:00')
            ->withoutOverlapping()
            ->onSuccess(fn() => \Log::info('Scheduler working Done :: backup:run --only-db --disable-notifications'))
            ->onFailure(fn() => \Log::info('Scheduler working Fail :: backup:run --only-db --disable-notifications'));

        // Backup cleanup at 01:20 AM
        $schedule->command('backup:clean --disable-notifications')
            ->dailyAt('5:00')
            ->withoutOverlapping()
            ->onSuccess(fn() => \Log::info('Scheduler working Done :: backup:clean --disable-notifications'))
            ->onFailure(fn() => \Log::info('Scheduler working Fail :: backup:clean --disable-notifications'));
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
