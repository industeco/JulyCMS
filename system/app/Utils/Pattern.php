<?php

namespace App\Utils;

class Pattern
{
    /**
     * 验证是否合法的网址
     *
     * @param  mixed $url
     * @return bool
     */
    public static function isUrl($url)
    {
        return is_string($url) && preg_match('/^(\/[a-z0-9\-_]+)+(\.html)?$/i', $url);
    }

    /**
     * 验证是否合法的 Twig 模板路径名
     *
     * @param  mixed $twig
     * @return bool
     */
    public static function isTwig($twig)
    {
        return is_string($twig) && preg_match('/^[a-z0-9\-_]+(\/[a-z0-9\-_]+)*\.twig$/', $twig);
    }
}
