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
        $clang = langcode('content_value.default');
        $configuration = [
            [
                'truename' => 'langcode.list',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '可用语言',
                    ],
                    'description' => [
                        'zh' => '语言主要用于内容翻译，后台界面暂不支持多语言（始终为中文）。',
                    ],
                    'value_type' => 'array',
                    'value' => config('jc.langcode.list'),
                ],
            ],
            [
                'truename' => 'langcode.content_value',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '内容默认语言',
                    ],
                    'description' => [
                        'zh' => '添加内容时默认使用的语言',
                    ],
                    'value_type' => 'string',
                    'value' => $clang,
                ],
            ],
            [
                'truename' => 'langcode.interface_value',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '界面值默认语言',
                    ],
                    'description' => [
                        'zh' => '添加界面值时默认使用的语言',
                    ],
                    'value_type' => 'string',
                    'value' => langcode('interface_value.default'),
                ],
            ],
            [
                'truename' => 'langcode.site_page',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '站点默认语言',
                    ],
                    'description' => [
                        'zh' => '网站页面默认使用哪种语言呈现',
                    ],
                    'value_type' => 'string',
                    'value' => langcode('site_page'),
                ],
            ],
            [
                'truename' => 'langcode.admin_page',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '后台界面默认语言',
                    ],
                    'description' => [
                        'zh' => '后台界面默认使用哪种语言呈现',
                    ],
                    'value_type' => 'string',
                    'value' => langcode('admin_page'),
                ],
            ],
            [
                'truename' => 'url',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '首页网址',
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
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '邮箱',
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
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '网站所有者（公司名）',
                    ],
                    'value_type' => 'string',
                    'value' => config('app.owner'),
                ],
            ],
            [
                'truename' => 'multi_language',
                'is_preset' => true,
                'config' => [
                    'langcode' => [
                        'interface_value' => 'zh',
                        'content_value' => $clang,
                    ],
                    'label' => [
                        'zh' => '多语言',
                    ],
                    'description' => [
                        'zh' => '若启用多语言，则可以通过带语言代码的网址访问，如：/en/index.html',
                    ],
                    'value_type' => 'boolean',
                    'value' => config('jc.multi_language', false),
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
