<?php

namespace App\Entity;

use App\Support\Arr;
use App\Support\Pocket;
use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;

abstract class TranslatableEntityBase extends EntityBase implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @var \App\Entity\EntityTranslationBase|null
     */
    protected $translation = null;

    /**
     * 获取绑定的翻译类
     *
     * @return string
     */
    public static function getTranslationClass()
    {
        return static::class.'Translation';
    }

    public static function isTranslatable()
    {
        return true;
    }

    /**
     * 设置实例当前语言
     *
     * @return string
     */
    public function setLangcode(string $langcode)
    {
        $this->attributes[$this->langcodeColumn] = $langcode;

        $this->pocket = null;

        return $this;
    }

    /**
     * 实体关联的翻译版本
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(static::getTranslationClass(), 'entity_id');
    }

    /**
     * 获取翻译版本
     *
     * @return \App\Entity\EntityTranslationBase|null
     */
    public function getTranslation()
    {
        return $this->translations()->where('langcode', $this->getLangcode())->first();
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

        $this->translation = $this->isTranslated() ? $this->getTranslation() : null;

        return $this;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->translation && $this->isTranslated()) {
            $attributes = $this->attributes;

            $this->setRawAttributes($this->translation->toEntityAttributes());

            $this->mergeAttributesFromClassCasts();

            return tap($this->attributes, function() use($attributes) {
                $this->setRawAttributes($attributes);
            });
        }

        $this->mergeAttributesFromClassCasts();

        return $this->attributes;
    }
}
