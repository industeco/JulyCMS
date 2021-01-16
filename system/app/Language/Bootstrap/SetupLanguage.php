<?php

namespace App\Language\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

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

        // 提取并设定内容语言（Request 所携带的内容数据的语言版本，内容数据是指通过后台表单提交的数据）
        if ($contentLangcode = $request->input('content_langcode')) {
            config(['lang.request_content' => $contentLangcode]);
        }

        // 判断并设定是否管理路由（以管理前缀开头的路由）
        $uri = trim(str_replace('\\', '/', $request->getRequestUri()), '/');
        $prefix = config('app.management_prefix', 'admin').'/';
        if (strncasecmp($uri, $prefix, strlen($prefix)) == 0) {
            config(['states.is_management_route' => true]);
            $uri = substr($uri, strlen($prefix));
        }

        // 提取并设定请求语言
        if ($langcode = langcode(explode('/', $uri)[0])) {
            config(['lang.request' => $langcode]);
        }
    }
}
