<?php

namespace App\Modules\Translation;

trait TranslatableTrait
{
    /**
     * 实例当前语言版本
     *
     * @var string|null
     */
    protected $contentLangcode = null;

    /**
     * 设置实例语言版本
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode)
    {
        $this->contentLangcode = $langcode;
        return $this;
    }

    /**
     * 获取实例当前语言
     *
     * @return string|null
     */
    public function getLangcode()
    {
        return $this->contentLangcode;
    }

    /**
     * 获取实例源语言
     *
     * @return string|null
     */
    public function getOriginalLangcode()
    {
        return $this->attributes['langcode'];
    }
}
