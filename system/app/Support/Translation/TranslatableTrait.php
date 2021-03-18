<?php

namespace App\Support\Translation;

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
     * @return string
     */
    public function getLangcode()
    {
        return $this->contentLangcode ?? $this->getOriginalLangcode();
    }

    /**
     * 获取实例源语言
     *
     * @return string
     */
    public function getOriginalLangcode()
    {
        return $this->attributes['langcode'];
    }

    /**
     * 判断内容为翻译版本还是源语言版本
     *
     * @return bool
     */
    public function isTranslated()
    {
        return $this->getLangcode() !== $this->getOriginalLangcode();
    }
}
