<?php

namespace App\Services\Translation;

trait TranslatableTrait
{
    use HasLangcodeTrait;

    /**
     * 翻译实例到指定语言
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode)
    {
        $this->setLangcode($langcode);

        return $this;
    }
}
