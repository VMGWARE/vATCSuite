<?php

namespace App\Console\Commands;

use Backpack\Settings\app\Models\Setting;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ConfigureSite extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site:configure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure the site with default roles, permissions and admin user.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create the default roles, if they don't exist.
        $this->info('Creating roles...');
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleUser = Role::firstOrCreate(['name' => 'user']);
        $this->info('Roles created.');

        // Create the default permissions, if they don't exist.
        $this->info('Creating permissions...');
        Permission::firstOrCreate(['name' => 'View Stored Files']);
        Permission::firstOrCreate(['name' => 'Perform File Operations']);
        Permission::firstOrCreate(['name' => 'File Search']);
        Permission::firstOrCreate(['name' => 'View Users']);
        Permission::firstOrCreate(['name' => 'Edit Users']);
        Permission::firstOrCreate(['name' => 'View Roles']);
        Permission::firstOrCreate(['name' => 'Edit Roles']);
        Permission::firstOrCreate(['name' => 'View Permissions']);
        Permission::firstOrCreate(['name' => 'Edit Permissions']);
        Permission::firstOrCreate(['name' => 'View Activity Logs']);
        Permission::firstOrCreate(['name' => 'Notification Management']);
        Permission::firstOrCreate(['name' => 'Site Configuration']);

        # Basic user permissions
        Permission::firstOrCreate(['name' => 'View Own Profile']);
        Permission::firstOrCreate(['name' => 'Edit Own Profile']);
        Permission::firstOrCreate(['name' => 'Create ATIS/AWOS']);
        Permission::firstOrCreate(['name' => 'View Own History']);
        Permission::firstOrCreate(['name' => 'Delete Own ATIS/AWOS']);
        $this->info('Permissions created.');

        // Assign the permissions to the roles
        $this->info('Assigning permissions to roles...');
        $roleAdmin->givePermissionTo(Permission::all());

        # Give basic user permissions
        $roleUser->givePermissionTo('View Own Profile');
        $roleUser->givePermissionTo('Edit Own Profile');
        $roleUser->givePermissionTo('Create ATIS/AWOS');
        $roleUser->givePermissionTo('View Own History');
        $roleUser->givePermissionTo('Delete Own ATIS/AWOS');
        $this->info('Roles and permissions created.');

        // Create the default admin user.
        $rootUser = env('APP_ROOT_USER', null);
        $rootPass = env('APP_ROOT_PASSWORD', null);
        if ($rootUser === null || $rootPass === null) {
            $this->info('Root user not configured. Please set APP_ROOT_USER and APP_ROOT_PASSWORD in your .env file.');
        } else {
            // Check if the user exists, or if an admin user exists.
            if (\App\Models\User::where('email', $rootUser)->exists() || \App\Models\User::role('admin')->exists()) {
                $this->info('Root user already exists. Skipping creation.');
            } else {
                // Create the user
                $this->info('Creating root user...');
                $rootUser = \App\Models\User::firstOrCreate([
                    'name' => $rootUser,
                    'email' => $rootUser,
                    'password' => bcrypt($rootPass),
                    'email_verified_at' => now(),
                ]);
                $rootUser->assignRole('admin');
                $this->info('Root user created.');
            }
        }

        // Create default settings, mainly analytics via matomo or google analytics.
        $this->info('Creating default settings...');

        // Matomo
        if (Setting::where('key', 'matomo_enable')->count() === 0) {
            $matomoEnable = new Setting();
            $matomoEnable->key = 'matomo_enable';
            $matomoEnable->name = 'Enable Matomo Analytics';
            $matomoEnable->description = 'Enable/Disable Matomo Analytics';
            $matomoEnable->value = env('MATOMO_ENABLE', '0');
            $matomoEnable->field = '{"name":"value","label":"Enabled","type":"checkbox"}';
            $matomoEnable->active = '1';
            $matomoEnable->save();
        }
        if (Setting::where('key', 'matomo_url')->count() === 0) {
            $matomoUrl = new Setting();
            $matomoUrl->key = 'matomo_url';
            $matomoUrl->name = 'Matomo URL';
            $matomoUrl->description = 'Matomo URL';
            $matomoUrl->value = env('MATOMO_URL', '');
            $matomoUrl->field = '{"name":"value","label":"Site URL","type":"text"}';
            $matomoUrl->active = '1';
            $matomoUrl->save();
        }
        if (Setting::where('key', 'matomo_site_id')->count() === 0) {
            $matomoSiteId = new Setting();
            $matomoSiteId->key = 'matomo_site_id';
            $matomoSiteId->name = 'Matomo Site ID';
            $matomoSiteId->description = 'Matomo Site ID';
            $matomoSiteId->value = env('MATOMO_SITE_ID', '');
            $matomoSiteId->field = '{"name":"value","label":"Site Tracking ID","type":"text"}';
            $matomoSiteId->active = '1';
            $matomoSiteId->save();
        }

        // Google Analytics
        if (Setting::where('key', 'google_analytics_enable')->count() === 0) {
            $googleAnalyticsEnable = new Setting();
            $googleAnalyticsEnable->key = 'google_analytics_enable';
            $googleAnalyticsEnable->name = 'Enable Google Analytics';
            $googleAnalyticsEnable->description = 'Enable/Disable Google Analytics';
            $googleAnalyticsEnable->value = env('GOOGLE_ANALYTICS_ENABLE', '0');
            $googleAnalyticsEnable->field = '{"name":"value","label":"Enabled","type":"checkbox"}';
            $googleAnalyticsEnable->active = '1';
            $googleAnalyticsEnable->save();
        }
        if (Setting::where('key', 'google_analytics_tracking_id')->count() === 0) {
            $googleAnalyticsTrackingId = new Setting();
            $googleAnalyticsTrackingId->key = 'google_analytics_tracking_id';
            $googleAnalyticsTrackingId->name = 'Google Analytics Tracking ID';
            $googleAnalyticsTrackingId->description = 'Google Analytics Tracking ID';
            $googleAnalyticsTrackingId->value = env('GOOGLE_ANALYTICS_TRACKING_ID', '');
            $googleAnalyticsTrackingId->field = '{"name":"value","label":"Tracking ID","type":"text"}';
            $googleAnalyticsTrackingId->active = '1';
            $googleAnalyticsTrackingId->save();
        }

        $this->info('Default settings created.');
    }
}
