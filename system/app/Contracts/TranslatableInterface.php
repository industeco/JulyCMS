<?php

namespace App\Contracts;

interface TranslatableInterface
{
    /**
     * 设置实例语言版本
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode);

    /**
     * 获取实例语言版本
     *
     * @return string|null
     */
    public function getLangcode();
}
