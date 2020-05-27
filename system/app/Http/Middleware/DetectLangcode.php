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
        if ($clang = $request->input('content_value_langcode')) {
            config(['request.langcode.content_value' => $clang]);
        }

        if ($ilang = $request->input('interface_value_langcode')) {
            config(['request.langcode.interface_value' => $ilang]);
        }

        config([
            'request.langcode.current_page' => $this->getCurrentPageLangcode($request->getRequestUri()),
        ]);

        return $next($request);
    }

    protected function getCurrentPageLangcode($uri)
    {
        $langcode = null;
        $uri = trim(str_replace('\\', '/', $uri), '/');
        if (strpos($uri, config('jc.admin.prefix') === 0)) {
            return config('jc.langcode.admin_page');
        }

        $dirs = explode('/', $uri);
        if ($langcode = $dirs[0] ?? null) {
            $langs = \langcode('all');
            if ($langcode && isset($langs[$langcode])) {
                return $langcode;
            }
        }

        return config('jc.langcode.site_page');
    }
}
