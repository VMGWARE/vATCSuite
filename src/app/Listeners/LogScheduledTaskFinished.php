<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogScheduledTaskFinished
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
    public function handle(ScheduledTaskFinished $event): void
    {
        //
    }
}
