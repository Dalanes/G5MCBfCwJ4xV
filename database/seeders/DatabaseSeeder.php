<?php

namespace Database\Seeders;

use App\Models\Flights;
use Database\Factories\FlightsFactory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();
        Flights::factory()->count(10)->create();
    }
}
