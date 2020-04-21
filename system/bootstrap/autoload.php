<?php

define('LARAVEL_START', microtime(true));

/**
 * July CMS version 2.0.0
 *
 * @author   jchenk <jchenk@live.com>
 */
define('JULY_VERSION', '2.0.0');

/*
|--------------------------------------------------------------------------
| Register Core Helpers
|--------------------------------------------------------------------------
|
| We cannot rely on Composer's load order when calculating the weight of
| each package. This line ensures that the core global helpers are
| always given priority one status.
|
*/

require __DIR__.'/../app/helpers.php';

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so that we do not have to worry about the
| loading of any our classes "manually". Feels great to relax.
|
*/

require __DIR__.'/../vendor/autoload.php';
