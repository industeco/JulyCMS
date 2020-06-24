<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class ContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getContentTypeData() as $record) {
            DB::table('content_types')->insert($record);
        }

        foreach ($this->getPivotData() as $record) {
            DB::table('content_field_content_type')->insert($record);
        }
    }

    protected function getContentTypeData()
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
                'content_type' => 'default',
                'content_field' => 'title',
                'delta' => 0,
            ],
            [
                'content_type' => 'basic',
                'content_field' => 'title',
                'delta' => 0,
            ],
            [
                'content_type' => 'basic',
                'content_field' => 'summary',
                'delta' => 1,
            ],
            [
                'content_type' => 'basic',
                'content_field' => 'content',
                'delta' => 2,
            ],
        ];
    }
}
