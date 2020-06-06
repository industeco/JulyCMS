<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->getData() as $tag) {
            DB::table('tags')->insert($tag);
        }
    }

    protected function getData()
    {
        $tags = [
            [
                'tag' => '_recommend',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_recommend',
                'langcode' => 'en',
            ],
            [
                'tag' => '_hot',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_hot',
                'langcode' => 'en',
            ],
        ];

        return array_map(function($record) {
            return array_merge($record, [
                'updated_at' => Date::now(),
                'created_at' => Date::now(),
            ]);
        }, $tags);
    }
}
