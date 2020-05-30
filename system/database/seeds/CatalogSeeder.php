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

        foreach ($this->getConfigData() as $record) {
            DB::table('configs')->insert($record);
        }
    }

    protected function getData()
    {
        return [
            [
                'truename' => 'main',
                'is_preset' => true,
            ]
        ];
    }

    protected function getConfigData()
    {
        $data = [
            [
                'keyname' => 'catalog.main',
                'group' => null,
                'name' => '默认目录',
                'description' => null,
                'data' => [],
            ]
        ];

        return array_map(function($record) {
            $record['data'] = json_encode($record['data'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            return $record;
        }, $data);
    }
}
