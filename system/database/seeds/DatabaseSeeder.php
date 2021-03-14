<?php

use Database\Seeds;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * 注册的数据填充器
     *
     * @var array
     */
    protected static $seeders = [
        Seeds\UserSeeder::class,
    ];

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();
        $this->call(static::$seeders);
        DB::commit();

        foreach (static::$seeders as $seeder) {
            if (method_exists($seeder, 'afterSeeding')) {
                $seeder::afterSeeding();
            }
        }

        static::$seeders = [];
    }

    /**
     * 登记数据填充器
     *
     * @param  string|array $seeders
     */
    public static function register($seeders)
    {
        static::$seeders = array_merge(static::$seeders, Arr::wrap($seeders));
    }
}
