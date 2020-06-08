<?php

namespace App\Http\Middleware;

use Closure;

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
        if ($clang = $request->input('content_langcode')) {
            config(['request.langcode.content' => $clang]);
        }

        config([
            'request.langcode.current_page' => $this->getPageLangcode($request->getRequestUri()),
        ]);

        return $next($request);
    }

    protected function getPageLangcode($uri)
    {
        $langcode = null;
        $uri = trim(str_replace('\\', '/', $uri), '/');
        if (strpos($uri, config('jc.admin_prefix', 'admin').'/') === 0) {
            return config('jc.langcode.admin_page');
        }

        $dirs = explode('/', $uri);
        if ($langcode = $dirs[0] ?? null) {
            $langs = \langcode('all');
            if ($langcode && isset($langs[$langcode])) {
                return $langcode;
            }
        }

        return config('jc.langcode.page');
    }
}
