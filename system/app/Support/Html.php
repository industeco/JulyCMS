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
        return $this->extract('/href="(\/[^"]*?(\.html|\/))"/');
    }

    /**
     * 提取图片链接
     *
     * @return array
     */
    public function extractImageLinks()
    {
        return $this->extract('/src="(\/[^"]*?\.(?:jpg|jpeg|gif|png|webp))"/');
    }

    /**
     * 提取 PDF 链接
     *
     * @return array
     */
    public function extractPdfLinks()
    {
        return $this->extract('/href="(\/[^"]*?\.pdf)"/');
    }

    protected function extract($reg)
    {
        preg_match_all($reg, $this->html, $matches, PREG_PATTERN_ORDER);
        return array_values(array_unique($matches[1]));
    }

    public function __toString()
    {
        return $this->html;
    }
}
