<?php

use Illuminate\Database\Seeder;
use App\Models;
use Illuminate\Support\Facades\DB;

class NodeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getData() as $record) {
            DB::table('node_types')->insert($record);
        }

        foreach ($this->getConfigData() as $record) {
            DB::table('configs')->insert($record);
        }

        foreach ($this->getPivotData() as $record) {
            DB::table('node_field_node_type')->insert($record);
        }
    }

    protected function getData()
    {
        return [
            [
                'truename' => 'basic',
                'is_preset' => false,
            ],
        ];
    }

    protected function getConfigData()
    {
        $data = [
            [
                'keyname' => 'node_type.basic',
                'group' => null,
                'name' => '基础页面',
                'description' => '如「关于我们页」，「联系我们页」等',
                'data' => [],
            ],
        ];

        return array_map(function($record) {
            $record['data'] = json_encode($record['data'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            return $record;
        }, $data);
    }

    protected function getPivotData()
    {
        return [
            [
                'node_type' => 'basic',
                'node_field' => 'title',
                'delta' => 0,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'content',
                'delta' => 1,
            ],
        ];
    }
}
