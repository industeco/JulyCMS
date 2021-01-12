<?php

namespace Installer;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class Installer
{
    /**
     * 检查安装环境
     *
     * @return array
     */
    public static function checkRequirements()
    {
        $results = [
            'PHP Version >= 7.2.5' => defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70205
        ];

        foreach ([
            'BCMath',
            'Ctype',
            'JSON',
            'Tokenizer',
            'XML',
            'Fileinfo',
            'Mbstring',
            'OpenSSL',
            'PDO_SQLite',
        ] as $requirement) {
            $results[$requirement] = extension_loaded($requirement);
        }

        return $results;
    }

    /**
     * 创建 SQLite 数据库文件
     *
     * @param  string $database
     * @return void
     */
    public static function prepareDatabase(string $database)
    {
        if (! is_file($database = database_path($database))) {
            touch($database);
        }
    }

    /**
     * 更新 .env 文件
     *
     * @param  array $settings
     * @return void
     */
    public static function updateEnv(array $settings)
    {
        Storage::disk('system')->put('.env', static::generateEnv($settings));
    }

    /**
     * 执行数据库迁移
     *
     * @return void
     */
    public static function migrate()
    {
        Artisan::call('migrate', [
            '--seed' => true,
            '--force' => true,
        ]);

        Storage::disk('system')->append('.env', "APP_INSTALLED=true\n");
        Artisan::call('cache:clear');
    }

    /**
     * 生成 .env 文件内容
     *
     * @param  array $settings
     * @return string
     */
    protected static function generateEnv($settings)
    {
        return implode("\n", [
            'APP_ENV='.config('app.env'),
            'APP_DEBUG='.(config('app.debug') ? 'true' : 'false'),
            'APP_KEY='.static::generateRandomKey(),
            'APP_URL='.($settings['app_url'] ?? null),
            'SITE_SUBJECT='.'"'.($settings['site_subject'] ?? null).'"',
            'DB_DATABASE='.($settings['db_database'] ?? null),
            'MAIL_TO_ADDRESS='.($settings['mail_to_address'] ?? null),
            'MAIL_TO_NAME='.preg_replace('/@.*$/', '', ($settings['mail_to_address'] ?? null)),
            '',
        ]);
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected static function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );
    }
}
