<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        //Appel du seeder blacklist
        $this->call([
            BlacklistedWordSeeder::class,
        ]);
    }
}
