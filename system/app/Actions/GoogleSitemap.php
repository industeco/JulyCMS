<?php

namespace App\Actions;

use App\Utils\Html;

class GoogleSitemap
{
    /**
     * 生成谷歌站点地图
     *
     * @param  array $urls
     * @return string
     */
    public static function render(array $urls)
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
