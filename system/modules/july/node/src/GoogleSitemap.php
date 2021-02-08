<?php

namespace July\Node;

use App\Utils\Html;

class GoogleSitemap
{
    /**
     * 生成谷歌站点地图
     */
    public static function build($langcode)
    {
        // 首页地址
        $home = rtrim(config('app.url'), '\\/');

        // pdf 信息
        $pdfs = [];

        // xml 文件
        $xml = '<'.'?xml version="1.0" encoding="UTF-8" ?'.'>'.PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"';
        $xml .= ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"';
        $xml .= ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9';
        $xml .= ' http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"';
        $xml .= ' xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'.PHP_EOL;

        $xml .= '<url><loc>'.$home.'/'.'</loc></url>'.PHP_EOL;

        $urls = NodeField::find('url')->getValueModel()->values();

        // 生成 xml 内容
        foreach (Catalog::default()->get_nodes() as $node) {
            $url = $urls[$node->getKey()] ?? null;
            if (!$url || $url === '/404.html') {
                continue;
            }

            $html = $node->translateTo($langcode)->fetchHtml();
            if (empty($html)) {
                continue;
            }

            $html = Html::make($html);

            $xml .= '<url><loc>'.$home.$url.'</loc>';
            foreach ($html->extractImageLinks() as $src) {
                $xml .= '<image:image><image:loc>'.$home.$src."</image:loc></image:image>".PHP_EOL;
            }
            $xml .= '</url>'.PHP_EOL;

            $pdfs = array_merge($pdfs, $html->extractPdfLinks());
        }

        foreach(array_unique($pdfs) as $pdf) {
            $xml .= '<url><loc>'.$home.$pdf.'</loc></url>'.PHP_EOL;
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
