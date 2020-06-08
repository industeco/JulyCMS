<?php

use Illuminate\Database\Seeder;
use App\Models\Catalog;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getCatalogData() as $record) {
            DB::table('catalogs')->insert($record);
        }
    }

    protected function getCatalogData()
    {
        return [
            [
                'truename' => 'main',
                'is_preset' => true,
                'label' => '默认目录',
                'description' => null,
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ]
        ];
    }
}
