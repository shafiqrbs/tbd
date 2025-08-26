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
        // Clean custom logs at 01:00 AM (before backup)
        $schedule->command('logs:clean-requests --days=10')
//            ->dailyAt('01:00')
                ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Manual log clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Manual log clean failed at ' . now());
            });

        // Daily DB backup at 01:10 AM
        $schedule->command('backup:run --only-db --disable-notifications')
//            ->dailyAt('01:10')
            ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Backup completed successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Backup failed at ' . now());
            });

        // Backup cleanup at 01:20 AM
        $schedule->command('backup:clean --disable-notifications')
//            ->dailyAt('01:20')
            ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Backup clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Backup clean failed at ' . now());
            });

        // Clean activity logs at 01:30 AM (after backup)
        $schedule->command('activitylog:clean --days=10 --force')
//            ->dailyAt('01:30')
            ->everyFiveMinutes()
            ->onSuccess(function () {
                \Log::info('✅ Activitylog clean successfully at ' . now());
            })
            ->onFailure(function () {
                \Log::error('❌ Activitylog clean failed at ' . now());
            });

        $schedule->call(function () {
            \Log::info('Environment: ' . app()->environment());
            \Log::info('Timezone: ' . config('app.timezone'));
            \Log::info('Current time: ' . now()->toDateTimeString());
        })->everyMinute();
        // Keep your test callback
        $schedule->call(function () {
            \Log::info('✅ Scheduler test at ' . now()->toDateTimeString());
        })->everyMinute();
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
