<?php

namespace App\Installer\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class InstallController extends Controller
{
    public function home()
    {
        return view('admin::install', [
            'requirements' => $this->checkForEnvironments(),
        ]);
    }

    protected function checkForEnvironments()
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

    public function install(Request $request)
    {
        if (! is_file($database = database_path($request->input('db_database')))) {
            touch($database);
        }
        Storage::disk('system')->put('.env', $this->getEnv($request));

        return response('');
    }

    public function migrate(Request $request)
    {
        config([
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

    protected function getEnv(Request $request)
    {
        return implode("\n", [
            'APP_ENV=' . config('app.env'),
            'APP_DEBUG=' . (config('app.debug') ? 'true' : 'false'),
            'APP_KEY=' . $this->generateRandomKey(),
            'APP_URL=' . $request->input('app_url'),
            'APP_OWNER=' . '"'.$request->input('app_owner').'"',
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
