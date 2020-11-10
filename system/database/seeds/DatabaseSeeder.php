<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use July\July;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $seeders = July::discoverSeeders();

        DB::beginTransaction();

        foreach ($seeders as $seeder) {
            $this->call($seeder);
        }

        DB::commit();

        foreach ($seeders as $seeder) {
            if (method_exists($seeder, 'afterSeeding')) {
                $seeder::afterSeeding();
            }
        }
    }
}
