<?php

use Illuminate\Database\Seeder;
use App\Models\Catalog;

class CatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $catalogs = [
            [
                'truename' => 'main',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'name' => [
                        'zh' => '默认目录',
                    ],
                ],
            ],
        ];

        foreach ($catalogs as $catalog) {
            Catalog::create($catalog);
        }
    }
}
