<?php

namespace App\Http\Controllers;

class AnyPage extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke($url)
    {
        $parameters = explode('/', strtolower($url));

        $langcode = langcode('site_page');
        if ($parameters[0] !== $langcode) {
            array_unshift($parameters, $langcode);
        }

        // 在 pages 目录查找文件
        $file = public_path('pages/'.implode('/', $parameters));
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        abort(404);
    }
}
