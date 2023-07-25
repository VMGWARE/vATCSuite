<?php

namespace App\Console\Commands;

use App\Jobs\GenerateSitemap;
use Illuminate\Console\Command;

class QueueSitemapGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue the sitemap generation.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        GenerateSitemap::dispatch()->delay(now()->addMinute());

        $this->info('Sitemap generation queued.');
    }
}
