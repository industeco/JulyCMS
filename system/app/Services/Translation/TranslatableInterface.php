<?php

namespace App\Services\Translation;

interface TranslatableInterface extends HasLangcodeInterface
{
    /**
     * 翻译实例语言
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode);
}
