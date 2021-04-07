<?php

namespace App\Bootstrap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;

class SetupLanguage
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        /** @var \Illuminate\Http\Request */
        $request = $app->make('request');

        // 标记是否管理请求
        if ($this->isManagementRequest($request)) {
            $request->offsetSet('is_management', true);
        }

        // 设定请求语言
        else {
            $lang = explode('/', $request->decodedPath())[0];
            $request->offsetSet('langcode', lang($lang)->getLangcode());
        }
    }

    /**
     * 判断是否管理请求
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function isManagementRequest(Request $request)
    {
        $path = $request->decodedPath().'/';
        $prefix = config('app.management_prefix', 'admin').'/';

        return strncasecmp($path, $prefix, strlen($prefix)) === 0;
    }
}
