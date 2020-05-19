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
            config(['current_content_lang' => $clang]);
        }

        if ($ilang = $request->input('interface_value_langcode')) {
            config(['current_interface_lang' => $ilang]);
        }

        $langcode = null;
        if (preg_match('~^\/?(\w+)(\/|$)~', $request->getRequestUri(), $matches)) {
            $langcode = $matches[1];
        }

        $langs = \langcode('all');
        if ($langcode && isset($langs[$langcode])) {
            config(['request_langcode' => $langcode]);
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
