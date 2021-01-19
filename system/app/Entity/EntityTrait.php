<?php

namespace App\Entity;

use Illuminate\Support\Str;

trait EntityTrait
{
    /**
     * 实体属性名册缓存
     *
     * @var array
     */
    protected static $attributeNamesCache = [
        'columns' => [],    // 固有属性
        'fields' => [],     // 实体字段
        'links' => [],      // 外联属性
    ];

    /**
     * 外联属性登记处
     *
     * @var array
     */
    protected static $links = [];

    /**
     * 实体的当前语言版本
     *
     * @var string|null
     */
    protected $contentLangcode = null;

    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName()
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * 获取实体路径
     *
     * @return string
     */
    public function getEntityPath()
    {
        return static::getEntityName().'/'.$this->getEntityId();
    }

    /**
     * 是否可翻译
     *
     * @return bool
     */
    public function isTranslatable()
    {
        return $this->hasColumn('langcode');
    }

    /**
     * 设置当前实例语言版本
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
     * 获取当前实例的语言
     *
     * @return string|null
     */
    public function getLangcode()
    {
        if ($this->isTranslatable()) {
            return $this->contentLangcode ?: $this->contentLangcode = $this->getColumnValue('langcode');
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPocketId()
    {
        return str_replace('\\', '/', static::class).'/'.$this->getEntityId();
    }

    /**
     * 判断是否包含名为 {$name} 的实体属性
     *
     * @param  string $name 属性名
     * @return bool
     */
    public function hasEntityAttribute($name)
    {
        return $this->hasColumn($name) || $this->hasLink($name) || $this->hasField($name);
    }

    /**
     * 获取实体属性（可能是：内部属性，或实体字段，或外联属性）
     *
     * @param  string  $key
     * @return mixed
     */
    public function getEntityAttribute($key)
    {
        if (! $key) {
            return null;
        }

        // 尝试内部属性
        if ($this->hasColumn($key)) {
            return $this->getColumnValue($key);
        }

        // 尝试外联属性
        elseif ($this->hasLink($key)) {
            return $this->getLinkValue($key);
        }

        // 尝试实体字段
        elseif ($this->hasField($key)) {
            return $this->getFieldValue($key);
        }

        return null;
    }

    /**
     * 获取常用属性
     *
     * @return array
     */
    public function entityToArray()
    {
        return array_merge(
            $this->columnsToArray(),
            $this->linksToArray(),
            $this->fieldsToArray()
        );
    }
}
