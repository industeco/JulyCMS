<?php

namespace App\Entity;

use App\Modules\Translation\TranslatableInterface;
use App\Model;
use App\Utils\Pocket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\EntityField\PathView;
use App\EntityField\PathAlias;
use App\EntityField\FieldBase;
use App\Modules\Translation\TranslatableTrait;

abstract class EntityBase extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 内建属性登记处
     *
     * @var array
     */
    protected static $columns = [];

    /**
     * 新建或更新时传入的原始数据
     *
     * @var array
     */
    protected $raw = [];

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
     * 获取实体 id
     *
     * @return int|string
     */
    public function getEntityId()
    {
        return $this->getKey();
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
     * 查找实体
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return static|null
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
     * @return static
     *
     * @throws \App\Entity\Exceptions\EntityNotFoundException
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
     * 获取实体路径别名（网址）
     *
     * @param  string|null $langcode 语言版本
     * @return string|null
     */
    public function getPathAlias(string $langcode = null)
    {
        if ($item = PathAlias::query()->where([
                'path' => $this->getEntityPath(),
                'langcode' => $langcode ?? $this->getLangcode(),
            ])->first()) {

            return $item->alias;
        }

        return null;
    }

    /**
     * 获取实体的渲染模板
     *
     * @param  string|null $langcode 语言版本
     * @return string|null
     */
    public function getPartialView(string $langcode = null)
    {
        if ($item = PartialView::query()->where([
                'path' => $this->getEntityPath(),
                'langcode' => $langcode ?? $this->getLangcode(),
            ])->first()) {

            return $item->view;
        }

        return null;
    }

    /**
     * 获取实体类型
     *
     * @return \App\Entity\EntityMoldBase
     */
    abstract public function getMold();

    /**
     * 判断是否包含名为 {$key} 的实体属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasEntityAttribute(string $key)
    {
        return $this->hasColumn($key) || $this->hasField($key);
    }

    /**
     * 获取实体属性（可能是：内建属性，或实体字段，或外联属性）
     *
     * @param  string  $key
     * @return mixed
     */
    public function getEntityAttribute(string $key)
    {
        if (! $key) {
            return null;
        }

        // 尝试内建属性
        if ($this->hasColumn($key)) {
            return $this->getColumnValue($key);
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
            $this->fieldsToArray()
        );
    }

    /**
     * 从实体属性数组中采集指定的列
     *
     * @param  array $keys 限定的列名
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        $attributes = $this->entityToArray();

        if ($keys && !in_array('*', $keys)) {
            $attributes = Arr::only($attributes, $keys);
        }

        return $attributes;
    }

    /**
     * 获取内建属性名表
     *
     * @return array
     */
    public function getColumnKeys()
    {
        if (is_array($keys = self::$keysCache['columns'][static::class] ?? null)) {
            return $keys;
        }

        if (static::$columns) {
            $keys = static::$columns;
        } else {
            $keys = $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable());
        }

        return self::$keysCache['columns'][static::class] = $keys;
    }

    /**
     * 获取字段属性名表
     *
     * @return array
     */
    public function getFieldKeys()
    {
        $mold = $this->getMold();
        $key = str_replace('\\', '/', get_class($mold)).'/'.$mold->getKey();

        if (is_array($keys = self::$keysCache['fields'][$key] ?? null)) {
            return $keys;
        }

        $keys = [];
        if (is_array($attributes = $this->cacheGetAttributes('fields'))) {
            $keys = array_keys($attributes);
        } else {
            $keys = $this->collectFields()->keys()->all();
        }

        return self::$keysCache['fields'][$key] = $keys;
    }

    /**
     * 获取内建属性集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectColumns()
    {
        $columns = $this->getColumnKeys();
        return collect($columns)->combine($columns);
    }

    /**
     * 获取实体字段对象集
     *
     * @return \Illuminate\Support\Collection|\App\EntityField\FieldBase[]
     */
    public function collectFields()
    {
        return collect();
    }

    /**
     * 判断是否包含名为 {$key} 的内建属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasColumn(string $key)
    {
        return in_array($key, $this->getColumnKeys());
    }

    /**
     * 判断是否包含名为 {$key} 的实体字段
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasField(string $key)
    {
        return in_array($key, $this->getFieldKeys());
    }

    /**
     * 获取内建属性的值
     *
     * @param  string $key
     * @return mixed
     */
    public function getColumnValue(string $key)
    {
        return $this->transformAttributeValue($key, $this->attributes[$key] ?? null);
    }

    /**
     * 获取实体字段的值
     *
     * @param  string $key 字段名
     * @return mixed
     */
    public function getFieldValue(string $key)
    {
        /** @var \App\EntityField\EntityFieldBase */
        $field = $this->collectFields()->get($key);

        return $this->transformAttributeValue($key, $field->getValue());
    }

    /**
     * 获取所有内建属性
     *
     * @return array
     */
    public function columnsToArray()
    {
        if (is_array($attributes = $this->cacheGetAttributes('columns'))) {
            return $attributes;
        }

        $attributes = [];
        foreach ($this->getColumnKeys() as $key) {
            $attributes[$key] = $this->attributes[$key] ?? null;
        }

        return $this->cachePutAttributes('columns', $this->transformAttributesArray($attributes));
    }

    /**
     * 收集所有字段属性并化为数组
     *
     * @return array
     */
    public function fieldsToArray()
    {
        if (is_array($attributes = $this->cacheGetAttributes('fields'))) {
            return $attributes;
        }

        $attributes = [];
        foreach ($this->collectFields() as $key => $field) {
            $attributes[$key] = $field->getValue();
        }

        return $this->cachePutAttributes('fields', $this->transformAttributesArray($attributes));
    }

    /**
     * 获取缓存的属性表
     *
     * @param  string $type 属性类型
     * @return array|null
     */
    final protected function cacheGetAttributes(string $type)
    {
        return self::$attributesCache[$type][$this->getEntityPath()] ?? null;
    }

    /**
     * 把属性表存入缓存
     *
     * @param  string $type 属性类型
     * @param  array $attributes 属性表
     * @return array
     */
    final protected function cachePutAttributes(string $type, array $attributes)
    {
        return self::$attributesCache[$type][$this->getEntityPath()] = $attributes;
    }

    /**
     * 转换属性值
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributeValue($key, $value)
    {
        return $this->transformModelValue($key, $value);
    }

    /**
     * 转换属性数组
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributesArray(array $attributes)
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableItems($attributes)
        );

        // Add the mutated attributes to the attributes array.
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        return $attributes;
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

        return $this->getEntityAttribute($key) ?? parent::getAttribute($key);
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $this->raw = $attributes;

        parent::fill($attributes);

        return $this;
    }

    /**
     * 实体保存
     *
     * @param  array  $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        DB::transaction(function () {
            $this->updateLinks();
            $this->updateFields();
        });

        $this->raw = [];

        return $saved;
    }

    /**
     * 更新实体字段
     *
     * @return void
     */
    protected function updateFields()
    {
        foreach ($this->collectFields() as $key => $field) {
            if (array_key_exists($key, $this->raw)) {
                $field->setValue($this->raw[$key]);
            }
        }
    }

    /**
     * Get the evaluated contents of the object.
     *
     * @return string
     */
    public function render()
    {
        return '';
    }

    /**
     * 获取实体渲染结果
     *
     * @return string
     */
    public function retrieveHtml()
    {
        $pocket = new Pocket($this, 'html');

        if ($html = $pocket->get()) {
            return $html->value();
        }

        $html = $this->render();
        $pocket->put($html);

        return $html;
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

    public static function boot()
    {
        parent::boot();

        static::deleting(function(EntityBase $entity) {
            $entity->collectFields()->each(function (FieldBase $field) {
                $field->deleteValue();
            });
        });
    }
}
