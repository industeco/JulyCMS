<?php

namespace App\Support\Translation;

interface TranslatableInterface
{
    /**
     * 获取实例语言
     *
     * @return string|null
     */
    public function getLangcode();

    /**
     * 设置实例语言
     *
     * @param  string
     * @return $this
     */
    public function setLangcode(string $langcode);

    /**
     * 获取实例源语言
     *
     * @return string|null
     */
    public function getOriginalLangcode();

    /**
     * 翻译实例到指定语言
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode);

    /**
     * 判断是否已翻译
     *
     * @return bool
     */
    public function isTranslated();
}
