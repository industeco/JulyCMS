<?php

use Illuminate\Database\Seeder;
use App\Models;
use Illuminate\Support\Facades\Date;
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
                'label' => '基础页面',
                'description' => '如「关于我们页」，「联系我们页」等',
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ],
        ];
    }

    protected function getPivotData()
    {
        $langcode = config('jc.langcode.content');
        return [
            [
                'node_type' => 'basic',
                'node_field' => 'title',
                'delta' => 0,
                'langcode' => $langcode,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'summary',
                'delta' => 1,
                'langcode' => $langcode,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'content',
                'delta' => 2,
                'langcode' => $langcode,
            ],
        ];
    }
}
