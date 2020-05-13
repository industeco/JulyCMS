<?php

use App\Models\Tag;
use Illuminate\Database\Seeder;

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
                'original' => '_recommend',
                'langcode' => 'en',
            ],
            [
                'tag' => '推荐',
                'is_preset' => true,
                'is_show' => false,
                'original' => '_recommend',
                'langcode' => 'zh',
            ],
        ];

        foreach ($tags as $tag) {
            Tag::create($tag);
        }
    }
}
