<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use July\Core\Config\PathAlias;
use July\Core\Entity\EntityManager;
use July\Core\Node\Node;
use July\Utils\GoogleSitemap;

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
        } elseif ($langcode !== langcode('frontend.default') || strpos($url, $langcode.'/') === 0) {
            abort(404);
        }

        if ($url === 'sitemap.xml') {
            return $this->getGoogleSitemap($langcode);
        }

        $url = $this->normalizeUrl($url);
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

        $content = GoogleSitemap::build($langcode);
        $disk->put($file, $content);

        return $content;
    }

    protected function normalizeUrl($url)
    {
        if ($url === '') {
            return 'index.html';
        }

        if (substr($url, -4) === '.htm') {
            return $url.'l';
        }

        return $url;
    }

    protected function getHtml($url, $langcode)
    {
        if ($entity = PathAlias::findEntityByAlias($url)) {
            return $entity->translateTo($langcode)->retrieveHtml();
        }

        return null;
    }
}
