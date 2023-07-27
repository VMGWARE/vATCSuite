<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->overrideConfigValues();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            \URL::forceScheme('https');
        }
    }

    /**
     * Override config values from the database.
     */
    protected function overrideConfigValues()
    {
        $config = [];
        if (config('settings.skin'))
            $config['backpack.base.skin'] = config('settings.skin');
        if (config('settings.show_powered_by'))
            $config['backpack.base.show_powered_by'] = config('settings.show_powered_by') == '1';
        config($config);
    }
}
