<?php

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $options = [
            [
                'truename' => 'content_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '默认内容语言',
                    ],
                    'description' => [
                        'zh' => '添加内容时的默认语言',
                    ],
                    'value' => ['en'],
                ],
            ],
            [
                'truename' => 'site_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '默认站点语言',
                    ],
                    'description' => [
                        'zh' => '网站页面默认使用哪种语言呈现',
                    ],
                    'value' => ['en'],
                ],
            ],
        ];

        foreach ($options as $option) {
            Config::create($option);
        }
    }
}
