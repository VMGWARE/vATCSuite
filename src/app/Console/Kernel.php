<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CleanUpExpiredATISAudioFiles;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule)
    {
        // Clean up expired ATIS audio files
        $schedule->job(new CleanUpExpiredATISAudioFiles, 'default', 'database')
            ->everyThirtyMinutes()
            ->withoutOverlapping()
            ->appendOutputTo("scheduler-output.log");

        // Generate sitemap
        $schedule->command('sitemap:generate')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
