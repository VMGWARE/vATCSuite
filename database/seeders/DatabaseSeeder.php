<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $airports = file_get_contents(database_path('seeds/airports.json'));
        $airports = json_decode($airports, true);

        collect($airports)->each(function ($airport) {
            \App\Models\Airport::create($airport);
        });
    }
}
