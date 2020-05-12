<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;

class SetLangcode
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
            config(['content_value_langcode' => $clang]);
            // View::share('content_value_langcode', $clang);
        }

        if ($ilang = $request->input('interface_value_langcode')) {
            config(['interface_value_langcode' => $ilang]);
            // View::share('interface_value_langcode', $ilang);
        }

        $langcode = null;
        if (preg_match('~^\/?(\w+)(\/|$)~', $request->getRequestUri(), $matches)) {
            $langcode = $matches[1];
        }

        $langs = langcode('all');
        if ($langcode && ($langs[$langcode] ?? null)) {
            config(['request_langcode' => $langcode]);
            // View::share('page_langcode', $langcode);
        } else {
            if ($langcode == 'admin') {
                config(['request_langcode' => config('jc.admin_page_lang')]);
            } else {
                config(['request_langcode' => config('jc.site_page_lang')]);
            }
        }

        return $next($request);
    }
}
