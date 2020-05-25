<?php

use App\Models\JulyConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JulyConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configuration = [
            [
                'truename' => 'languages',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '可用语言',
                    ],
                    'description' => [
                        'zh' => '语言主要用于内容翻译，后台界面暂不支持多语言（始终为中文）。',
                    ],
                    'value_type' => 'array',
                    'value' => ['en','zh'],
                ],
            ],
            [
                'truename' => 'content_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '内容默认语言',
                    ],
                    'description' => [
                        'zh' => '添加内容时默认使用的语言',
                    ],
                    'value_type' => 'string',
                    'value' => 'en',
                ],
            ],
            [
                'truename' => 'interface_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '界面值默认语言',
                    ],
                    'description' => [
                        'zh' => '添加界面值时默认使用的语言',
                    ],
                    'value_type' => 'string',
                    'value' => 'zh',
                ],
            ],
            [
                'truename' => 'site_page_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '站点默认语言',
                    ],
                    'description' => [
                        'zh' => '网站页面默认使用哪种语言呈现',
                    ],
                    'value_type' => 'string',
                    'value' => 'en',
                ],
            ],
            [
                'truename' => 'admin_page_lang',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '后台界面默认语言',
                    ],
                    'description' => [
                        'zh' => '后台界面默认使用哪种语言呈现',
                    ],
                    'value_type' => 'string',
                    'value' => 'zh',
                ],
            ],
            [
                'truename' => 'url',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '首页网址',
                    ],
                    'description' => [
                        'zh' => '',
                    ],
                    'value_type' => 'string',
                    'value' => config('app.url'),
                ],
            ],
            [
                'truename' => 'email',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '邮箱',
                    ],
                    'description' => [
                        'zh' => '',
                    ],
                    'value_type' => 'string',
                    'value' => config('mail.to.address'),
                ],
            ],
            [
                'truename' => 'owner',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => 'en',
                    ],
                    'label' => [
                        'zh' => '网站所有者（公司名）',
                    ],
                    'description' => [
                        'zh' => '',
                    ],
                    'value_type' => 'string',
                    'value' => config('app.owner'),
                ],
            ],
        ];

        DB::transaction(function() use($configuration) {
            foreach ($configuration as $entry) {
                $entry['config'] = json_encode($entry['config'], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
                DB::table('july_configs')->insert($entry);
            }
        });
    }
}
