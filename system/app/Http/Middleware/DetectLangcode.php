<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class DetectLangcode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // 设置内容语言
        if ($clang = $request->input('langcode') ?? $request->input('content_langcode')) {
            config()->set('request.language.content', $clang);
        }

        // 设置页面语言
        config()->set('request.language.frontend', $this->getFrontendLangcode($request));

        return $next($request);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getFrontendLangcode($request)
    {
        $uri = trim(str_replace('\\', '/', $request->getRequestUri()), '/');
        if (strpos($uri, config('app.management_prefix', 'admin').'/') === 0) {
            config()->set('request.is_backend', true);
            App::setLocale(langcode('backend'));
            return config('language.backend');
        }

        $dirs = explode('/', $uri);
        if (lang()->isLangcode($langcode = $dirs[0] ?? null)) {
            return $langcode;
        }

        return config('language.frontend');
    }
}
