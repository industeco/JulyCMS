<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

/**
 * July CMS version 3.0.0
 *
 * @author   jchenk <jchenk@live.com>
 */
define('JULY_VERSION', '3.0.0');

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels great to relax.
|
*/

require __DIR__.'/system/bootstrap/autoload.php';


// class Test
// {
//     use \App\Concerns\CacheResultTrait;

//     /**
//      * 获取所有列和字段值
//      *
//      * @param  array $keys 限定键名
//      * @return array
//      */
//     public function gather(array $keys = ['*'])
//     {
//         // 尝试从缓存获取数据
//         if ($attributes = $this->cacheGet(__FUNCTION__)) {
//             $attributes = $attributes->value();
//         }

//         // 生成属性数组
//         else {
//             $attributes = [
//                 'id' => 1,
//                 'label' => '标签',
//                 'description' => '描述',
//             ];
//         }

//         if ($keys && !in_array('*', $keys)) {
//             $attributes = \Illuminate\Support\Arr::only($attributes, $keys);
//         }

//         return $attributes;
//     }

//     public function getCache()
//     {
//         return $this->resultCache;
//     }
// }

// $test = new Test;

// dump($test->cacheGet('gather', 'id'));

// dump($test->getCache());

// dump($test->gather());

// dump($test->getCache());

// exit;


/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/system/bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
