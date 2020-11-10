<?php

namespace July\Core\Entity;

use App\Model as AppModel;
use App\Utils\Pocket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use July\Core\EntityField\EntityFieldBase;

abstract class EntityBase extends AppModel implements EntityInterface
{
    use EntityTrait;

    public static $fieldIds = [];

    /**
     * 当前实例的语言
     *
     * @var string|null
     */
    protected $contentLangcode = null;

    /**
     * 获取实体 id
     *
     * @return int|string
     */
    public function getEntityId()
    {
        return $this->getKey();
    }

    /**
     * 设置当前实例的语言
     *
     * @return self
     */
    public function translateTo(string $langcode = null)
    {
        $this->contentLangcode = $langcode;

        return $this;
    }

    /**
     * 获取当前实例的语言
     *
     * @return self
     */
    public function getLangcode()
    {
        return $this->contentLangcode ?? $this->getAttribute('langcode');
    }

    /**
     * 查找实体
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return self|null
     */
    public static function find($id, array $columns = ['*'])
    {
        $instance = new static;
        return $instance->forwardCallTo($instance->newQuery(), 'find', [$id, $columns]);
    }

    /**
     * 查找实体，找不到则抛出错误
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return self|null
     *
     * @throws \July\Core\Entity\Exceptions\EntityNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*'])
    {
        try {
            $instance = new static;
            return $instance->forwardCallTo($instance->newQuery(), 'findOrFail', [$id, $columns]);
        } catch (\Throwable $th) {
            if ($th instanceof ModelNotFoundException) {
                $th = Exceptions\EntityNotFoundException::wrap($th);
            }
            throw $th;
        }
    }

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass()
    {
        $morphMap = Relation::morphMap();

        if (! empty($morphMap) && in_array(static::class, $morphMap)) {
            return array_search(static::class, $morphMap, true);
        }

        return static::getEntityName();
    }

    /**
     * Retrieve the actual class name for a given morph class.
     *
     * @param  string  $class
     * @return string
     */
    public static function getActualClassNameForMorph($class)
    {
        if ($actualClass = Arr::get(Relation::morphMap() ?: [], $class, null)) {
            return $actualClass;
        }

        return EntityManager::resolveName($class) ?? $class;
    }

    /**
     * 判断是否可翻译（属性表中包含 langcode 属性）
     *
     * @return bool
     */
    public static function isTranslatable()
    {
        return (new static)->hasAttribute('langcode');
    }

    /**
     * 判断是否包含指定属性
     *
     * @param  string $attribute
     * @return bool
     */
    public function hasAttribute(string $attribute)
    {
        return array_key_exists($attribute, $this->attributes) ||
            in_array($attribute, $this->fillable) ||
            $this->hasGetMutator($attribute);
    }

    /**
     * 初步获取实体所有外部字段及其值
     *
     * @return array
     */
    protected function getFieldAttributes()
    {
        $attributes = [];
        foreach ($this->getEntityFields() as $field) {
            if ($field instanceof EntityFieldBase) {
                $attributes[$field->getEntityName()] = $field->getValue($this);
            }
        }

        return $attributes;
    }

    /**
     * 收集所有字段属性并化为数组
     *
     * @return array
     */
    public function fieldsToArray()
    {
        // 获取原始字段值
        $attributes = $this->getFieldAttributes();

        // 对字段值做 mutate 转换（如果有对应的 getFieldNameAttribute 方法）
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // 对字段值做 cast 转换
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        return $attributes;
    }

    /**
     * 获取常用属性
     *
     * @param  string|null $langcode
     * @return array
     */
    public function gather()
    {
        return array_merge(
            $this->attributesToArray(),
            $this->fieldsToArray()
        );
    }

    /**
     * 获取所有实体字段
     *
     * @return \Illuminate\Support\Collection
     */
    public function getEntityFields()
    {
        return collect();
    }

    /**
     * 判断是否包含指定字段
     *
     * @param  string $key
     * @return bool
     */
    public function hasEntityField(string $key)
    {
        if (! isset(static::$fieldIds[static::class])) {
            static::$fieldIds[static::class] = $this->getEntityFields()->keys()->all();
        }

        return in_array($key, static::$fieldIds[static::class]);
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (! $key) {
            return;
        }

        if ($this->hasEntityField($key)) {
            return $this->getFieldValue($key);
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) ||
            array_key_exists($key, $this->casts) ||
            $this->hasGetMutator($key) ||
            $this->isClassCastable($key)) {
            return $this->getAttributeValue($key);
        }

        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(self::class, $key)) {
            return;
        }

        return $this->getRelationValue($key);
    }

    /**
     * 获取实体字段的值
     *
     * @param  string $key 字段名
     * @return mixed
     */
    public function getFieldValue(string $key)
    {
        /**
         * @var \July\Core\EntityField\EntityFieldBase
         */
        $field = $this->getEntityFields()->get($key);

        return $this->transformModelValue($key, $field->getValue($this));
    }

    /**
     * Dynamically retrieve attributes on Content.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        if (! $key) {
            return;
        }

        if ($this->hasEntityField($key)) {
            return $this->getFieldValue($key);
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) || in_array($key, $this->fillable) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        // Here we will determine if the model base class itself contains this given key
        // since we don't want to treat any of those methods as relationships because
        // they are all intended as helper methods and none of these are relations.
        if (method_exists(HasAttributes::class, $key)) {
            return;
        }

        // If the key already exists in the relationships array, it just means the
        // relationship has already been loaded, so we'll just return it out of
        // here because there is no need to query within the relations twice.
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        // If the "attribute" exists as a method on the model, we will just assume
        // it is a relationship and will load and return results from the query
        // and hydrate the relationship's value on the "relationships" array.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }
}
