<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use July\Core\Node\Node;

class AnyPage extends Controller
{
    /**
     * 返回任意页面
     *
     * @return View
     */
    public function __invoke(Request $request)
    {
        $url = $request->getRequestUri();
        if (preg_match('/[A-Z]/', $url)) {
            return Redirect::to(strtolower($url));
        }

        $url = trim(str_replace('\\', '/', $url), '/');

        $langcode = langcode('frontend');
        if (config('jc.language.multiple')) {
            if (!lang($langcode)->isAccessible()) {
                abort(404);
            }
            if (strpos($url, $langcode.'/') === 0) {
                $url = substr($url, strlen($langcode.'/'));
            }
        } else {
            if (strpos($url, $langcode.'/') === 0 || $langcode !== langcode('frontend.default')) {
                abort(404);
            }
        }

        if ($url === 'sitemap.xml') {
            return $this->getGoogleSitemap($langcode);
        }

        $url = $this->completeUrl($url);
        if ($url === '404.html') {
            abort(404);
        }

        if ($html = $this->getHtml($url, $langcode)) {
            return $html;
        }

        abort(404);
    }

    protected function getGoogleSitemap($langcode)
    {
        $file = 'pages/'.$langcode.'/sitemap.xml';
        $disk = Storage::disk('storage');
        if ($disk->exists($file)) {
            return $disk->get($file);
        }

        $content = build_google_sitemap($langcode);
        $disk->put($file, $content);

        return $content;
    }

    protected function completeUrl($url)
    {
        if ($url === '') {
            return 'index.html';
        }

        if (substr($url, -4) === '.htm') {
            return $url.'l';
        }

        if (substr($url, -5) !== '.html') {
            return $url.'/index.html';
        }

        return $url;
    }

    protected function getHtml($url, $langcode)
    {
        $file = 'pages/'.$langcode.'/'.$url;
        $disk = Storage::disk('storage');
        if ($disk->exists($file)) {
            return $disk->get($file);
        }

        if ($node = Node::findByUrl($url, $langcode)) {
            if ($html = $node->getHtml($langcode)) {
                return $html;
            }
        }

        return null;
    }
}
