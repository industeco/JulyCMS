<?php

namespace App\Support;

class Html
{
    /**
     * @var string
     */
    protected $html = '';

    public function __construct($html)
    {
        $this->html = $html;
    }

    public function getHtml()
    {
        return $this->html;
    }

    /**
     * 提取页面链接
     *
     * @return array
     */
    public function extractPageLinks()
    {
        preg_match_all('/href="(\/[^"]*?(\.html|\/))"/', $this->html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }

    /**
     * 提取图片链接
     *
     * @return array
     */
    public function extractImageLinks()
    {
        preg_match_all('/src="(\/[^"]*?\.(?:jpg|jpeg|gif|png|webp))"/', $this->html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }

    /**
     * 提取 PDF 链接
     *
     * @return array
     */
    public function extractPdfLinks()
    {
        preg_match_all('/href="(\/[^"]*?\.pdf)"/', $this->html, $matches, PREG_PATTERN_ORDER);
        return array_unique($matches[1]);
    }
}
