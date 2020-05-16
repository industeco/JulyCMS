<?php

if ('POST' != ($_SERVER['REQUEST_METHOD'] ?? null)) {
    header('location:/install');
    exit;
}

define('INSTALL_PATH', __DIR__);
define('LARAVEL_PATH', dirname(INSTALL_PATH) . '/system');

require INSTALL_PATH.'/php/helpers.php';
require INSTALL_PATH.'/php/SettingsValidator.php';
require INSTALL_PATH.'/php/DotenvEditor.php';
require INSTALL_PATH.'/php/EnvEntry.php';

$env = new JulyInstaller\DotenvEditor;

$envFile = LARAVEL_PATH.'/.env';
$envExample = LARAVEL_PATH.'/.env.example';
if (! is_file($envFile)) {
    if (is_file($envExample)) {
        copy($envExample, $envFile);
    } else {
        exit_error('找不到 .env 文件');
    }
}
if (is_file($envExample)) {
    unlink($envExample);
}
$env->load($envFile);

if ($env->get('APP_INSTALLED')) {
    exit_error('已安装');
}

/**
 * 更新配置
 */

// SQLite 数据库文件名
$db_file = trim($_POST['db_file'] ?? '');
if (! SettingsValidator::isValidSqliteFile($db_file)) {
    exit_error('参数错误：数据库文件名');
}

// 用户名
$admin_truename = trim($_POST['admin_name'] ?? '');
if (! SettingsValidator::isValidUserName($admin_truename)) {
    exit_error('参数错误：用户名');
}

// 密码
$admin_password = $_POST['admin_password'] ?? '';
if (! SettingsValidator::isValidPassword($admin_password)) {
    exit_error('参数错误：密码');
}

// 域名
$app_url = $_POST['app_url'] ?? '';
if (! SettingsValidator::isValidUrl($app_url)) {
    exit_error('参数错误：域名');
}

// 邮箱
$email = $_POST['email'] ?? '';
if (! SettingsValidator::isValidEmail($email)) {
    exit_error('参数错误：邮箱');
}

// 网站所有者
$app_owner = trim($_POST['owner'] ?? '');
if (strlen($app_owner) === 0) {
    exit_error('参数错误：企业名');
}

$env->set('APP_URL', $app_url);
$env->set('APP_OWNER', $app_owner);
$env->set('DB_CONNECTION', 'sqlite');
$env->set('DB_DATABASE', $db_file);
$env->set('MAIL_TO_ADDRESS', $email);

$env->set('APP_INSTALLED', true);

$env->save();
$env->close();


// 启动 Laravel
require LARAVEL_PATH.'/bootstrap/autoload.php';
$app = require_once LARAVEL_PATH.'/bootstrap/app.php';

$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

try {
    // 生成 APP_KEY
    Artisan::call('key:generate', [
        '--force' => true,
    ]);

    // 创建 SQLite 数据库
    if (! preg_match('/\.db3?$/i', $db_file)) {
        $db_file .= '.db3';
    }
    $db = new SQLite3(database_path($db_file));
    $db->close();

    Config::set('admin_truename', $admin_truename);
    Config::set('admin_password', $admin_password);

    Artisan::call('migrate', [
        '--seed' => true,
        '--force' => true,
    ]);

} catch (\Throwable $ex) {
    exit(json_encode([
        'success' => false,
        'msg' => $ex->getMessage(),
    ]));
}

$env->load(LARAVEL_PATH.'/.env');
$env->set('APP_INSTALLED', true);
$env->save();
$env->close();

exit(json_encode([
    'success' => true,
    'msg' => '',
]));
