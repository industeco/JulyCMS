<?php

// define('INSTALL_PATH', __DIR__);
// define('LARAVEL_PATH', dirname(INSTALL_PATH) . '/system');

// require INSTALL_PATH.'/php/DotenvEditor.php';
// require INSTALL_PATH.'/php/EnvEntry.php';

// $env = new JulyInstaller\DotenvEditor;
// $env->load(LARAVEL_PATH.'/.env');

// print('<pre>');
// print_r($env->env);

// exit;

// July CMS 基于 Laravel 7，要求 PHP >= 7.2.5
define('JULYCMS_MINIMUM_PHP_VERSION_ID', 70205);

/**
 * 检查 PHP 版本
 */
if (!defined('PHP_VERSION_ID') || PHP_VERSION_ID < JULYCMS_MINIMUM_PHP_VERSION_ID) {
    exit('July CMS 需要 PHP 版本为 7.2.5 或以上');
}

/*
 * Check for JSON extension
 */
if (!function_exists('json_decode')) exit('JSON PHP Extension is required to install July CMS');

/*
 * PHP headers
 */
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

include 'view/main.htm';

exit;
