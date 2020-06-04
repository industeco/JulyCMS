<?php

use Illuminate\Database\Seeder;
use App\Models\Catalog;
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
        foreach ($this->getData() as $record) {
            DB::table('catalogs')->insert($record);
        }
    }

    protected function getData()
    {
        return [
            [
                'truename' => 'main',
                'is_preset' => true,
                'label' => '默认目录',
                'description' => null,
            ]
        ];
    }
}
