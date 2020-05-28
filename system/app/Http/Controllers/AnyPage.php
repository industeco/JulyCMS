<?php

namespace App\Http\Controllers;

use App\Models\Node;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnyPage extends Controller
{
    /**
     * 返回任意页面
     *
     * @return View
     */
    public function __invoke($url = '')
    {
        if (preg_match('/[A-Z]/', $url)) {
            return Redirect::to(strtolower($url));
        }

        $langcode = langcode('current_page');
        if (!config('jc.multi_language') && $langcode !== langcode('site_page')) {
            abort(404);
        }

        if (!config('jc.langcode.permissions.'.$langcode.'.site_page')) {
            abort(404);
        }

        $url = format_request_uri($url);

        if ($url === '404.html') {
            abort(404);
        }

        if ($url === 'sitemap.xml') {
            $disk = Storage::disk('storage');
            $file = 'pages/'.$langcode.'/sitemap.xml';
            if ($disk->exists($file)) {
                $content = $disk->get($file);
            } else {
                $content = build_google_sitemap($langcode);
                $disk->put($file, $content);
            }
            return $content;
        }

        if (! Str::endsWith($url, '.html')) {
            $url .= '/index.html';
        }

        if ($node = Node::findByUrl($url, $langcode)) {
            if ($html = $node->getHtml($langcode)) {
                return $html;
            }
        }

        abort(404);
    }
}
