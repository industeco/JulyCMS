<?php

namespace App\Http\Controllers;

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
