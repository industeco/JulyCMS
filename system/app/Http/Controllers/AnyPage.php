<?php

namespace App\Http\Controllers;

use App\Models\Node;
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
            $file = 'sitemap.xml';
            $disk = Storage::disk('public');
            if ($disk->exists($file)) {
                $content = $disk->get($file);
            } else {
                $content = build_google_sitemap();
                $disk->put('sitemap.xml', $content);
            }
            return $content;
        }

        if (! Str::endsWith($url, '.html')) {
            $url .= '/index.html';
        }

        if ($html = Node::retrieveHtml($url)) {
            return $html;
        }

        abort(404);
    }
}
