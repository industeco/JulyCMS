<?php

namespace App\Entity;

use App\EntityField\EntityPathAlias;
use App\EntityField\EntityView;
use App\EntityField\FieldBase;
use App\Modules\Translation\TranslatableInterface;
use App\Modules\Translation\TranslatableTrait;
use App\Model;
use App\Utils\Pocket;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

abstract class EntityBase extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 实体字段 id 缓存
     *
     * @var array
     */
    protected static $fields = [];

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
     * 获取实体路径别名（网址）
     *
     * @return string|null
     */
    public function getPathAlias()
    {
        return EntityPathAlias::findAlias($this);
    }

    /**
     * 获取实体视图
     *
     * @return string|null
     */
    public function getView()
    {
        return EntityView::findView($this);
    }

    /**
     * 获取实体类型类
     *
     * @return string
     */
    abstract public static function getMoldClass();

    /**
     * 获取实体类型类
     *
     * @return string
     */
    public static function getMoldForeignKeyName()
    {
        return 'mold_id';
    }

    /**
     * 实体所属类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mold()
    {
        return $this->belongsTo($this->getMorphClass(), $this->getMoldForeignKeyName());
    }

    /**
     * 获取实体类型
     *
     * @return \App\Entity\EntityMoldBase|null
     */
    public function getMold()
    {
        if ($this->exists) {
            return $this->mold;
        } elseif ($mold_id = $this->attributes['mold_id'] ?? null) {
            $mold = $this->getMoldClass();
            return $mold::find($mold_id);
        }
        return null;
    }

    /**
     * 判断是否包含名为 {$key} 的实体属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasEntityAttribute(string $key)
    {
        return $key && ($this->hasColumn($key) || $this->hasField($key));
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
     * 获取内建属性集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectColumns()
    {
        $columns = $this->getColumns();
        return collect($columns)->combine($columns);
    }

    /**
     * 实体字段
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    abstract public function fields();

    /**
     * 获取实体字段对象集
     *
     * @return \Illuminate\Support\Collection|\App\EntityField\FieldBase[]
     */
    abstract public function collectFields();

    /**
     * 获取字段属性名表
     *
     * @return array
     */
    public function getFields()
    {
        if (empty($this->fields)) {
            $this->fields = $this->collectFields()->keys()->all();
        }
        return $this->fields;
    }

    /**
     * 判断实体字段是否存在
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasField(string $key)
    {
        return in_array($key, $this->getFields());
    }

    /**
     * 获取实体字段值
     *
     * @param  string $key 字段名
     * @return mixed
     */
    public function getFieldValue(string $key)
    {
        /** @var \App\EntityField\FieldBase */
        $field = $this->collectFields()->get($key);

        return $this->transformAttributeValue($key, $field->getValue());
    }

    /**
     * 获取所有实体字段值
     *
     * @return array
     */
    public function fieldsToArray()
    {
        $attributes = [];
        foreach ($this->collectFields() as $key => $field) {
            $attributes[$key] = $field->getValue();
        }

        return $this->transformAttributesArray($attributes);
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

        return EntityManager::resolve($class) ?? $class;
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
