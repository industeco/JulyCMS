<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => 'JulyCMS',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Shanghai',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        // Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,

        // July\JulyServiceProvider::class,

        // Modules
        Installer\ModuleServiceProvider::class,
        Specs\ModuleServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [
        'App' => Illuminate\Support\Facades\App::class,
        'Arr' => Illuminate\Support\Arr::class,
        'Artisan' => Illuminate\Support\Facades\Artisan::class,
        'Auth' => Illuminate\Support\Facades\Auth::class,
        'Blade' => Illuminate\Support\Facades\Blade::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'Bus' => Illuminate\Support\Facades\Bus::class,
        'Cache' => Illuminate\Support\Facades\Cache::class,
        'Config' => Illuminate\Support\Facades\Config::class,
        'Cookie' => Illuminate\Support\Facades\Cookie::class,
        'Crypt' => Illuminate\Support\Facades\Crypt::class,
        'DB' => Illuminate\Support\Facades\DB::class,
        'Eloquent' => Illuminate\Database\Eloquent\Model::class,
        'Event' => Illuminate\Support\Facades\Event::class,
        'File' => Illuminate\Support\Facades\File::class,
        'Gate' => Illuminate\Support\Facades\Gate::class,
        'Hash' => Illuminate\Support\Facades\Hash::class,
        'Http' => Illuminate\Support\Facades\Http::class,
        'Lang' => Illuminate\Support\Facades\Lang::class,
        'Log' => Illuminate\Support\Facades\Log::class,
        'Mail' => Illuminate\Support\Facades\Mail::class,
        'Notification' => Illuminate\Support\Facades\Notification::class,
        'Password' => Illuminate\Support\Facades\Password::class,
        'Queue' => Illuminate\Support\Facades\Queue::class,
        'Redirect' => Illuminate\Support\Facades\Redirect::class,
        'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request' => Illuminate\Support\Facades\Request::class,
        'Response' => Illuminate\Support\Facades\Response::class,
        'Route' => Illuminate\Support\Facades\Route::class,
        'Schema' => Illuminate\Support\Facades\Schema::class,
        'Session' => Illuminate\Support\Facades\Session::class,
        'Storage' => Illuminate\Support\Facades\Storage::class,
        'Str' => Illuminate\Support\Str::class,
        'URL' => Illuminate\Support\Facades\URL::class,
        'Validator' => Illuminate\Support\Facades\Validator::class,
        'View' => Illuminate\Support\Facades\View::class,
    ],

    // 安装标记
    'is_installed' => env('APP_INSTALLED', false),

    // demo 模式
    'is_demo' => env('APP_DEMO', false),

    // 指定主题
    'theme' => 'backend',

    // 管理前缀
    'management_prefix' => 'manage',

    // 登记配置管理类
    'settings' => [
        \App\Settings\Language::class,
        \App\Settings\SiteInformation::class,
        // \App\Settings\UserInterface::class,
        \App\Settings\Redirections::class,
    ],

    // 登记实体
    'entities' => [
        //
    ],

    // 登记实体字段类型
    'field_types' => [
        \App\EntityField\FieldTypes\File::class,
        \App\EntityField\FieldTypes\Html::class,
        \App\EntityField\FieldTypes\Text::class,
        \App\EntityField\FieldTypes\Any::class,
    ],

    // 是否允许通过实体路径访问
    'entity_path_accessible' => false,

    // 全局字段分组
    'field_groups' => [
        'taxonomy' => [
            'label' => '分类和标签',   // 分组面板标题
            'expanded' => true,    // 是否默认展开
        ],
        'page_present' => [
            'label' => '网址和模板',
            'expanded' => true,
        ],
        'page_meta' => [
            'label' => 'META 信息',
            'expanded' => true,
        ],
    ],

    'field_parameters_schema' => [
        'default',
        'options',
        'placeholder',

        // 改为在字段类型中设置
        // 'type' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     'default' => 'string',
        // ],

        // 改为在字段表中设置
        // 'maxlength' => [
        //     'cast' => 'int',
        //     'translatable' => false,
        //     'overwritable' => false,
        // ],

        // 改为在字段表和铸模表中设置
        // 'required' => [
        //     'cast' => 'boolean',
        //     'translatable' => false,
        //     'overwritable' => true,
        //     'default' => false,
        // ],
        // 'helpertext' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => true,
        // ],

        // 取消以下设置
        // 'pattern' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        // ],
        // 'file_bundle' => [
        //     'cast' => 'string',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     // 'enumerators' => ['image', 'file'],
        // ],
        // 'multiple' => [
        //     'cast' => 'boolean',
        //     'translatable' => false,
        //     'overwritable' => false,
        //     'default' => false,
        // ],
    ],

    // 主菜单
    'main_menu' => [
        // 内容
        'content' => [
            'title' => '内容',
            'icon' => 'create',
            'route' => null,
            'children' => [
                // [
                //     'title' => '内容',
                //     'icon' => null,
                //     'route' => 'nodes.index',   // 路由名，或数组（格式：[路由名, 参数 1, 参数 2, ...]），下同
                //     'children' => [],
                // ],
            ],
        ],

        // 结构
        'structure' => [
            'title' => '结构',
            'icon' => 'device_hub',
            'route' => null,
            'children' => [
                // [
                //     'title' => '类型',
                //     'icon' => null,
                //     'route' => 'node_types.index',
                //     'children' => [],
                // ],
                // [
                //     'title' => '目录',
                //     'icon' => null,
                //     'route' => 'catalogs.index',
                //     'children' => [],
                // ],
                // [
                //     'title' => '标签',
                //     'icon' => null,
                //     'route' => 'tags.index',
                //     'children' => [],
                // ],
            ],
        ],

        // 组件
        'modules' => [
            'title' => '组件',
            'icon' => 'view_column',
            'route' => null,
            'children' => [
                [
                    'title' => '规格',
                    'icon' => null,
                    'route' => 'specs.index',
                    'children' => [],
                ],
            ],
        ],

        // 文件管理
        'file_manager' => [
            'title' => '文件',
            'icon' => 'folder',
            'route' => 'media.index',
            'children' => [],
        ],

        // 配置管理
        'settings' => [
            'title' => '配置',
            'icon' => 'settings',
            'route' => null,
            'children' => [],
        ],
    ],

    // 表单验证
    'validation' => [
        'file_bundles' => [
            'image' => [
                'png','jpg','jpeg','webp','bmp','svg','gif','ico',
            ],

            'file' => [
                'pdf', 'doc', 'ppt', 'xls', 'dwg',
            ],
        ],

        'patterns' => [
            'url' => '/^(\\/[a-z0-9\\-_]+)+\\.html$/',
            'twig' => '/^(\\/[a-z0-9\\-_]+)+(\\.html)?\\.twig$/',
            'email' => '\'email\'',
        ],
    ],
];
