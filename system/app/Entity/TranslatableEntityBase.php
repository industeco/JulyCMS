<?php

namespace App\Entity;

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
        if (! $this->translation) {
            $this->translation = $this->translations->keyBy('langcode')->get($this->getLangcode());
        }

        return $this->translation;
    }
}
