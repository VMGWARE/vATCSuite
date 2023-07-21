<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledBackgroundTaskFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogScheduledBackgroundTaskFinished
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ScheduledBackgroundTaskFinished $event): void
    {
        //
    }
}
