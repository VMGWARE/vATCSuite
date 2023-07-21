<?php

namespace App\Listeners;

use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogScheduledTaskStarting
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
    public function handle(ScheduledTaskStarting $event): void
    {
        //
    }
}
