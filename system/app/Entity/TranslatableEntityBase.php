<?php

namespace App\Entity;

use App\Support\Arr;
use App\Support\Lang;
use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;
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

    /**
     * 缓存生成的 html
     *
     * @param  string $html
     * @param  string $url
     * @param  string|null $langcode
     * @return $this
     */
    protected function cacheHtml(string $html, string $url, ?string $langcode = null)
    {
        if (preg_match('/\.html?$/i', $url)) {
            $url = ltrim($url, '/');

             // 在不带语言的路径下生成 html 文件
            Storage::disk('public')->put($url, $html);

            if ($langcode) {
                // 在带语言的路径下生成 html 文件
                Storage::disk('public')->put(strtolower($langcode).'/'.$url, $html);
            }
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
        foreach (Lang::getAccessibleLangnames() as $code => $langname) {
            $languages[$code] = [
                'is_active' => $code === $langcode,
                'url' => '/'.strtolower($code).$url,
                'name' => [
                    'native' => $nativeNames[$code] ?? $code,
                    $code => $langname,
                ],
            ];
        }

        return $languages;
    }
}
