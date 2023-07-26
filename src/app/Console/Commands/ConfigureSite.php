<?php

namespace App\Console\Commands;

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
            return;
        } else {
            // Check if the user exists
            if (\App\Models\User::where('email', $rootUser)->exists()) {
                $this->info('Root user already exists.');
                return;
            }

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
}
