<?php

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
        $this->call(ConfigSeeder::class);
        $this->call(AdministratorsTableSeeder::class);
        $this->call(NodeFieldSeeder::class);
        $this->call(NodeTypeSeeder::class);
        $this->call(CatalogSeeder::class);
    }
}
