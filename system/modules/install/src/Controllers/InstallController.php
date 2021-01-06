<?php

namespace Installer\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class InstallController extends Controller
{
    /**
     * 安装步骤 0：展示安装界面
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        return view('backend::install', [
            'requirements' => $this->checkRequirements(),
        ]);
    }

    /**
     * 安装步骤 1：重写 .env 文件
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function install(Request $request)
    {
        if (! is_file($database = database_path($request->input('db_database')))) {
            touch($database);
        }
        Storage::disk('system')->put('.env', $this->getEnv($request));

        return response('');
    }

    /**
     * 安装步骤 2：运行迁移文件
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function migrate(Request $request)
    {
        config()->set([
            'admin_name' => $request->input('admin_name'),
            'admin_password' => $request->input('admin_password'),
        ]);

        Artisan::call('migrate', [
            '--seed' => true,
            '--force' => true,
        ]);

        Storage::disk('system')->append('.env', "APP_INSTALLED=true\n");
        Artisan::call('cache:clear');

        return response('');
    }

    /**
     * 检查安装环境
     *
     * @return array
     */
    protected function checkRequirements()
    {
        $results = [];
        $results['PHP Version >= 7.2.5'] = defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 70205;

        $phpRequirements = [
            'BCMath',
            'Ctype',
            'JSON',
            'Tokenizer',
            'XML',
            'Fileinfo',
            'Mbstring',
            'OpenSSL',
            'PDO_SQLite',
        ];

        foreach ($phpRequirements as $requirement) {
            $results[$requirement . ' PHP 扩展'] = extension_loaded($requirement);
        }

        return $results;
    }

    /**
     * 生成 .env 文件内容
     *
     * @param  \Illuminate\Http\Request $request
     * @return string
     */
    protected function getEnv($request)
    {
        return implode("\n", [
            'APP_ENV=' . config('app.env'),
            'APP_DEBUG=false',
            'APP_KEY=' . $this->generateRandomKey(),
            'APP_URL=' . $request->input('app_url'),
            'SITE_SUBJECT=' . '"'.$request->input('site_subject').'"',
            'DB_DATABASE=' . $request->input('db_database'),
            'MAIL_TO_ADDRESS=' . $request->input('mail_to_address'),
            'MAIL_TO_NAME=' . preg_replace('/@.*$/', '', $request->input('mail_to_address')),
            '',
        ]);
    }

    /**
     * Generate a random key for the application.
     *
     * @return string
     */
    protected function generateRandomKey()
    {
        return 'base64:'.base64_encode(
            Encrypter::generateKey(config('app.cipher'))
        );
    }
}
