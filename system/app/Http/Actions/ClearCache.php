<?php

namespace App\Http\Actions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ClearCache extends ActionBase
{
    protected static $routeName = 'clear-cache';

    protected static $title = '清除缓存';

    public function __invoke(Request $request)
    {
        // 清除缓存
        Artisan::call('cache:clear');

        // 清除视图缓存
        Artisan::call('view:clear');

        return true;
    }
}
