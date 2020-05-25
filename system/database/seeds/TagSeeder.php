<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;
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
        $tags = [
            [
                'tag' => '_recommend',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_recommend',
                'langcode' => 'en',
            ],
            [
                'tag' => '_推荐',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_recommend',
                'langcode' => 'zh',
            ],
            [
                'tag' => '_hot',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_hot',
                'langcode' => 'en',
            ],
            [
                'tag' => '_热门',
                'is_preset' => true,
                'is_show' => false,
                'original_tag' => '_hot',
                'langcode' => 'zh',
            ],
        ];

        DB::transaction(function() use($tags) {
            foreach ($tags as $tag) {
                DB::table('tags')->insert($tag);
            }
        });
    }
}
