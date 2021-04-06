<?php

namespace App\Http\Actions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

abstract class ActionBase extends Controller
{
    protected static $routeUrl = null;

    protected static $routeName = null;

    protected static $title = null;

    public static function getRouteUrl()
    {
        $url = static::$routeUrl ?? Str::kebab(class_basename(static::class));
        return 'actions/'.$url;
    }

    public static function getRouteName()
    {
        $name = static::$routeName ?? Str::kebab(class_basename(static::class));
        return 'actions.'.$name;
    }

    public static function getTitle()
    {
        return static::$title ?? ucwords(Str::snake(class_basename(static::class)));
    }

    public static function defineRoutes()
    {
        Route::prefix(config('app.management_prefix', 'admin'))
            ->middleware(['web', 'admin', 'auth'])
            ->post(static::getRouteUrl(), static::class)
            ->name(static::getRouteName());
    }

    abstract public function __invoke(Request $request);
}
