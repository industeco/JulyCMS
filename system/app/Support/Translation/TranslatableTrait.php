<?php

namespace App\Support\Translation;

trait TranslatableTrait
{
    protected $langcodeColumn = 'langcode';

    /**
     * @var string|null
     */
    protected $translationLangcode = null;

    /**
     * 设置实例当前语言
     *
     * @return string
     */
    public function setLangcode(string $langcode)
    {
        $this->translationLangcode = $langcode;

        return $this;
    }

    /**
     * 获取实例当前语言
     *
     * @return string
     */
    public function getLangcode()
    {
        return $this->translationLangcode ?? $this->getOriginalLangcode();
    }

    /**
     * 获取实例源语言
     *
     * @return string
     */
    public function getOriginalLangcode()
    {
        return $this->original[$this->langcodeColumn] ?? $this->attributes[$this->langcodeColumn] ?? langcode('content');
    }

    /**
     * 翻译实例内容
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode)
    {
        $this->setLangcode($langcode);

        return $this;
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
