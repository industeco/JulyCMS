<?php

namespace App\Modules\Translation;

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
     * 获取实例当前语言
     *
     * @return string|null
     */
    public function getLangcode();

    /**
     * 获取实例源语言
     *
     * @return string|null
     */
    public function getOriginalLangcode();
}
