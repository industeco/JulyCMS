<?php

use Database\Seeds;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * 注册的数据填充器
     *
     * @var array
     */
    protected static $seeders = [];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $this->call(Seeds\UserSeeder::class);
        $this->call(static::$seeders);
        DB::commit();

        foreach (static::$seeders as $seeder) {
            if (method_exists($seeder, 'afterSeeding')) {
                $seeder::afterSeeding();
            }
        }

        static::$seeders = [];
    }

    public function registerSeeders(array $seeders)
    {
        static::$seeders = array_merge(static::$seeders, $seeders);
    }
}
