<?php

namespace App\Http\Actions;

use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use July\Node\Catalog;
use July\Node\NodeField;
use Specs\Spec;

class BuildGoogleSitemap extends ActionBase
{
    protected static $routeName = 'build-google-sitemap';

    protected static $title = '重建谷歌站点地图';

    public function __invoke(Request $request)
    {
        $urls = [];

        // 节点网址
        $aliases = NodeField::find('url')->getValueModel()->values();
        foreach (Catalog::default()->get_nodes() as $node) {
            $url = $aliases[$node->getKey()] ?? null;
            if (!$url || $url === '/404.html') {
                continue;
            }
            $urls[$url] = $node->fetchHtml();
        }

        // 规格网址
        foreach (Spec::all() as $spec) {
            foreach ($spec->getRecords() as $record) {
                $urls['/specs/'.$spec->getKey().'/records/'.$record['id']] = null;
            }
        }

        // 生成谷歌站点地图
        Storage::disk('public')->put('sitemap.xml', $this->render($urls));

        return response('');
    }

    /**
     * 生成谷歌站点地图
     *
     * @param  array $urls
     * @return string
     */
    protected function render(array $urls)
    {
        // 首页地址
        $home = rtrim(config('app.url'), '\\/');
        $data = [
            'home' => $home,
            'urls' => [],
            'pdfs' => [],
        ];

        foreach ($urls as $url => $content) {
            if (is_int($url)) {
                $url = $content;
                $content = null;
            }
            $url = ltrim($url, '\\/');

            if (!$content && is_file(public_path($url))) {
                $content = file_get_contents(public_path($url));
            }
            $content = Html::make($content);

            // 提取图片
            $images = [];
            foreach ($content->extractImageLinks() as $img) {
                $images[$home.'/'.ltrim($img, '\\/')] = null;
            }
            $data['urls'][$home.'/'.$url] = [
                'images' => $images,
            ];

            // 提取 PDF
            foreach ($content->extractPdfLinks() as $pdf) {
                $data['pdfs'][$home.'/'.ltrim($pdf, '\\/')] = null;
            }
        }

        // return html_compress(view('google-sitemap', $data)->render());
        return html_compress(app('twig')->render('google-sitemap.twig', $data));
    }
}
