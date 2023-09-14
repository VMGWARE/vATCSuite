<?php

namespace App\Console\Commands;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class Uptime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uptime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Writes to a file the current time.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $time = time();
        $this->info('Writing to file...');
        file_put_contents(storage_path('app/uptime.txt'), $time);
        $this->info('Done! Wrote ' . $time . ' to file.');
    }
}
