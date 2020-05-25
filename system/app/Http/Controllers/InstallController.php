<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

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
        $env = $this->getEnv($request->all());

        $envContent = '';
        foreach ($env as $key => $value) {
            $envContent .= $key.'='.$value."\n";
        }
        Storage::disk('public')->put('system/.env', $envContent);

        if (! is_file($database = database_path($env['DB_DATABASE']))) {
            touch($database);
        }

        return response('');
    }

    public function migrate(Request $request)
    {
        $disk = Storage::disk('public');
        $env = $disk->get('system/.env');
        $env .= "APP_INSTALLED=true\n";
        $disk->put('system/.env', $env);

        app('config')->set('admin_name', $request->input('admin_name'));
        app('config')->set('admin_password', $request->input('admin_password'));

        Artisan::call('migrate', [
            '--seed' => true,
            '--force' => true,
        ]);

        return response('');
    }

    protected function getEnv(array $values)
    {
        return [
            'APP_ENV' => config('app.env'),
            'APP_DEBUG' => config('app.debug') ? 'true' : 'false',
            'APP_KEY' => $this->generateRandomKey(),
            'APP_URL' => $values['app_url'],
            'APP_OWNER' => '"'.$values['app_owner'].'"',
            'DB_DATABASE' => $values['db_database'],
            'MAIL_TO_ADDRESS' => $values['mail_to_address'],
            'MAIL_TO_NAME' => substr($values['mail_to_address'], 0, strpos($values['mail_to_address'], '@')),
        ];
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
