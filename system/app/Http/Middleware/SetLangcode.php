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
        if ($clang = $request->input('content_value_lang')) {
            config(['content_value_lang' => $clang]);
            // View::share('content_value_lang', $clang);
        }

        if ($ilang = $request->input('interface_value_lang')) {
            config(['interface_value_lang' => $ilang]);
            // View::share('interface_value_lang', $ilang);
        }

        $langcode = null;
        if (preg_match('~^\/?(\w+)(\/|$)~', $request->getRequestUri(), $matches)) {
            $langcode = $matches[1];
        }
        if ($langcode && in_array($langcode, langcode('available'))) {
            config(['request_lang' => $langcode]);
            // View::share('page_lang', $langcode);
        } else {
            if ($langcode == 'admin') {
                config(['request_lang' => config('app.langcode.admin_page')]);
            } else {
                config(['request_lang' => config('app.langcode.site_page')]);
            }
        }
        return $next($request);
    }
}
