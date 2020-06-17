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
        foreach ($this->getNodeTypeData() as $record) {
            DB::table('node_types')->insert($record);
        }

        foreach ($this->getPivotData() as $record) {
            DB::table('node_field_node_type')->insert($record);
        }
    }

    protected function getNodeTypeData()
    {
        return [
            [
                'truename' => 'default',
                'is_preset' => true,
                'label' => '默认类型',
                'description' => '批量导入数据时用作默认类型，该类型的内容支持类型转换。预设类型，不可编辑，不可删除。',
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ],
            [
                'truename' => 'basic',
                'is_preset' => false,
                'label' => '基础类型',
                'description' => '可用于静态页面，如「关于我们」页',
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ],
        ];
    }

    protected function getPivotData()
    {
        return [
            [
                'node_type' => 'default',
                'node_field' => 'title',
                'delta' => 0,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'title',
                'delta' => 0,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'summary',
                'delta' => 1,
            ],
            [
                'node_type' => 'basic',
                'node_field' => 'content',
                'delta' => 2,
            ],
        ];
    }
}
