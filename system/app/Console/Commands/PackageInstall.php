<?php

namespace App\Console\Commands;

use App\Support\Archive;
use Illuminate\Console\Command;

class PackageInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pkg:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成安装包';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $allowList = [
            'files/',
            'images/',
            'system/' => [
                'app/**',
                'bootstrap/*',
                'config/*.php',
                'database/' => [
                    'migrations/*.php',
                    'seeds/*.php',
                ],
                'language/*.php',
                'modules/**',
                'resources/**',
                'routes/*.php',
                'storage/' => [
                    'framework/**/',
                    'logs/',
                    'settings/',
                ],
                '.env.production' => '.env',
                'artisan',
                'composer.json',
                'composer.lock',
            ],
            'themes/' => [
                'backend/**',
                'frontend/*',
            ],
            '.editorconfig',
            '.htaccess',
            'index.php',
            'LICENSE',
            'robots.txt',
        ];

        return Archive::create('julycms_install.zip')->from($allowList)->zip();

        // $whiteList = [
        //     'files/' => '',
        //     'images/' => '',
        //     'system/app/*' => '',
        //     'system/bootstrap/cache/' => '',
        //     'system/bootstrap/app.php' => '',
        //     'system/bootstrap/autoload.php' => '',
        //     'system/config/*' => '',
        //     'system/database/factories/*' => '',
        //     'system/database/migrations/*' => '',
        //     'system/database/seeds/*' => '',
        //     'system/language/*' => '',
        //     'system/routes/*' => '',
        //     'system/storage/framework/cache/data/' => '',
        //     'system/storage/framework/sessions/' => '',
        //     'system/storage/framework/views/' => '',
        //     'system/storage/logs/' => '',
        //     'system/storage/pages/' => '',
        //     'system/.env.production' => 'system/.env',
        //     'system/artisan' => '',
        //     'system/composer.json' => '',
        //     'system/composer.lock' => '',
        //     'themes/admin/*' => '',
        //     'themes/default/css/' => '',
        //     'themes/default/fonts/' => '',
        //     'themes/default/images/' => '',
        //     'themes/default/js/' => '',
        //     'themes/default/template/' => '',
        //     '.editorconfig' => '',
        //     '.htaccess' => '',
        //     'index.php' => '',
        //     'LICENSE' => '',
        //     'robots.txt' => '',
        // ];

        // $pkg = public_path('julycms_install.zip');
        // return package_files($pkg, get_file_list($whiteList));
    }
}
