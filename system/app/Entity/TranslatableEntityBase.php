<?php

namespace App\Entity;

use App\Support\Arr;
use App\Support\Lang;
use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;
use Illuminate\Contracts\Database\Eloquent\CastsInboundAttributes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * @var array
     */
    protected $translationAttributes = [];

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
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        if ($this->isTranslated()) {
            $attributes = Arr::except($attributes, ['id', 'entity_id', 'langcode']);
            $this->translations()->updateOrCreate([
                'entity_id' => $this->getKey(),
                'langcode' => $this->getLangcode(),
            ], $attributes);

            $raw = Arr::except($attributes, $this->getFillable());
            $this->setRaw($raw)->updateFields()->clearRaw();

            return $this->touch();
        }

        if ($this->immutable) {
            $attributes = Arr::except($attributes, $this->immutable);
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * 设置实例当前语言
     *
     * @return string
     */
    public function setLangcode(string $langcode)
    {
        $this->translationLangcode = $langcode;

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

        if ($this->isTranslated()) {
            $this->translation = $this->getTranslation();
        } else {
            $this->translation = null;
        }

        $this->translationAttributes = $this->translation ? $this->translation->toEntityAttributes() : $this->attributes;

        return $this;
    }

    /**
     * 获取实体字段值
     *
     * @param  string|int $key 字段 id
     * @return mixed
     */
    public function getFieldValue($key)
    {
        $values = $this->getFieldValues([$key])[$key] ?? [];

        $valueKey = $this->getEntityId().'/'.$this->getLangcode();
        $originalKey = $this->getEntityId().'/'.$this->getOriginalLangcode();

        return $this->transformAttributeValue($key, $values[$valueKey] ?? $values[$originalKey] ?? null);
    }

    /**
     * 获取所有实体字段值
     *
     * @return array
     */
    public function fieldsToArray()
    {
        $fieldValues = static::getFieldValues();

        $key = $this->getEntityId().'/'.$this->getLangcode();
        $originalKey = $this->getEntityId().'/'.$this->getOriginalLangcode();

        $attributes = [];
        foreach ($fieldValues as $field => $values) {
            $attributes[$field] = $values[$key] ?? $values[$originalKey] ?? null;
        }

        return $this->transformAttributesArray($attributes);
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->translation) {
            return $this->getTranslationAttributes();
        }

        return $this->getModelAttributes();
    }

    /**
     * Get all of the translated attributes on the model.
     *
     * @return array
     */
    public function getTranslationAttributes()
    {
        $this->mergeTranslationAttributesFromClassCasts();

        return $this->translationAttributes;
    }

    /**
     * Merge the cast class attributes back into the model.
     *
     * @return void
     */
    protected function mergeTranslationAttributesFromClassCasts()
    {
        foreach ($this->classCastCache as $key => $value) {
            $caster = $this->resolveCasterClass($key);

            $this->translationAttributes = array_merge(
                $this->translationAttributes,
                $caster instanceof CastsInboundAttributes
                       ? [$key => $value]
                       : $this->normalizeCastClassResponse($key, $caster->set($this, $key, $value, $this->translationAttributes))
            );
        }
    }

    /**
     * 缓存生成的 html
     *
     * @param  string $html
     * @param  string $url
     * @return $this
     */
    protected function cacheHtml(string $html, string $url)
    {
        if (preg_match('/\.html?$/i', $url)) {
            Storage::disk('public')->put(ltrim($url, '/'), $html);
        }

        return $this;
    }

    /**
     * 生成用于在页面中实现语言切换功能的数组
     *
     * @param  string $url
     * @return array
     */
    public function getLanguageOptions(string $url)
    {
        $url = '/'.ltrim($url, '/');
        $langcode = $this->getLangcode();

        $nativeNames = Lang::getNativeLangnames();
        $languages = [];
        foreach (Lang::getAccessibleLangnames($langcode) as $code => $langname) {
            $languages[$code] = [
                'is_active' => $code === $langcode,
                'url' => '/'.strtolower($code).$url,
                'langname' => $langname,
                'native' => $nativeNames[$code] ?? $code,
            ];
        }

        return $languages;
    }
}
