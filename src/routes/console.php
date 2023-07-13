<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// FIXME: This is a temporary solution to the setup process

Artisan::command('setup', function () {
    // Make sure that a .env file exists
    $this->comment('Checking for .env file');
    if (!file_exists(base_path('.env'))) {
        // Copy the .env.example file to .env
        $this->comment('Creating .env file');
        copy(base_path('.env.example'), base_path('.env'));
        // Generate an app key
        $this->call('key:generate');
        // Tell the user to update the .env file
        $this->comment('Please update the .env file with your database credentials/api keys and run this command again');
    }

    // run the migrations
    $this->comment('Running migrations');
    $this->call('migrate');

    // Seed the database
    $this->comment('Seeding the database');
    $this->call('db:seed');

    // if the storage link doesn't exist, create it
    $this->comment('Checking for storage link');
    if (!file_exists(base_path('public/storage'))) {
        $this->call('storage:link');
        $this->comment('Storage link created');
    } else {
        $this->comment('Storage link already exists');
    }

    // Finished
    $this->comment('Setup complete');
});
