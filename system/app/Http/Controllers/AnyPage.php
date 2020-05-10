<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnyPage extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke($url = '')
    {
        $url = trim(strtolower(str_replace('\\', '/', $url)), '\\/');

        if (basename($url) === 'sitemap.xml') {
            $file = public_path('pages/'.$url);
            if (file_exists($file)) {
                $content = @file_get_contents($file);
            } else {
                $content = build_google_sitemap();
                Storage::disk('public')->put('sitemap.xml', $content);
            }
            return $content;
        }

        if (! Str::endsWith($url, '.html')) {
            $url .= '/index.html';
        }

        $langcode = langcode('site_page');
        if (! Str::startsWith($url, $langcode.'/')) {
            $url = $langcode.'/'.$url;
        }

        // 在 pages 目录查找文件
        $file = public_path('pages/'.$url);
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        abort(404);
    }
}
