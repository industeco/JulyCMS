<?php

namespace App\EntityField;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\Entity\Exceptions\InvalidEntityException;
use App\EntityField\FieldTypes\FieldTypeManager;
use App\Modules\Translation\TranslatableInterface;
use App\Modules\Translation\TranslatableTrait;
use App\Models\ModelBase;

abstract class FieldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 绑定的实体名
     *
     * @var string|null
     */
    protected $boundEntityName = null;

    /**
     * 字段所属实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $entity;

    /**
     * 中间数据缓存
     *
     * @var array
     */
    protected $cached = [];

    /**
     * 获取字段绑定实体的实体名
     *
     * @return string|null
     */
    public function getBoundEntityName()
    {
        if ($this->boundEntityName) {
            return $this->boundEntityName;
        } elseif ($this->entity) {
            return $this->entity->getEntityName();
        }
        return null;
    }

    /**
     * 获取绑定的实体
     *
     * @return \App\Entity\EntityBase|null
     */
    public function getBoundEntity()
    {
        return $this->entity;
    }

    /**
     * 绑定到实体
     *
     * @param  \App\Entity\EntityBase $entity
     * @return $this
     *
     * @throws \App\Entity\Exceptions\InvalidEntityException
     */
    public function bindEntity(EntityBase $entity)
    {
        $class = $this->boundEntityName ? EntityManager::resolve($this->boundEntityName) : null;
        if (!$class || $entity instanceof $class) {
            $this->entity = $entity;
            return $this;
        } else {
            throw new InvalidEntityException('当前字段无法绑定到实体：'.get_class($entity));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        if (!$this->contentLangcode && $this->entity) {
            return $this->entity->getLangcode();
        }
        return $this->contentLangcode;
    }

    /**
     * label 属性的 Mutator
     *
     * @param  string|null $label
     * @return string
     */
    public function getLabelAttribute($label)
    {
        if ($this->pivot) {
            return trim($this->pivot->label ?? $label);
        }
        return trim($label);
    }

    /**
     * description 属性的 Mutator
     *
     * @param  string|null $description
     * @return string
     */
    public function getDescriptionAttribute($description)
    {
        if ($this->pivot) {
            return trim($this->pivot->description ?? $description);
        }
        return trim($description);
    }

    /**
     * is_required 属性的 Mutator
     *
     * @param  bool|int $required
     * @return bool
     */
    public function getIsRequiredAttribute($required)
    {
        if ($this->pivot) {
            return (bool) ($this->pivot->is_required ?? $required);
        }
        return (bool) $required;
    }

    /**
     * helpertext 属性的 Mutator
     *
     * @param  string|null $helpertext
     * @return string
     */
    public function getHelpertextAttribute($helpertext)
    {
        if ($this->pivot) {
            return trim($this->pivot->helpertext ?? $helpertext);
        }
        return trim($helpertext);
    }

    /**
     * 获取字段参数
     *
     * @return array
     */
    public function getParameters()
    {
        // 尝试从缓存获取数据
        if ($result = $this->pipeCache(__FUNCTION__)) {
            return $result->value();
        }

        // 获取字段的所有相关参数
        /** @var \Illuminate\Database\Eloquent\Collection */
        $parameters = FieldParameters::ofField($this)->get()
            ->keyBy(function($item) {
                return $item->langcode.','.$item->mold_id;
            });

        // 类型 id
        $mold_id = $this->entity->mold_id;

        // 当前内容语言 + 当前类型 id
        $key = $this->getLangcode().','.$mold_id;
        if ($parameters->has($key)) {
            return $parameters->get($key)->parameters;
        }

        // 类型源语言 + 当前类型 id
        $key = $this->entity->getMold()->getOriginalLangcode().','.$mold_id;
        if ($parameters->has($key)) {
            return $parameters->get($key)->parameters;
        }

        // 字段源语言 + null
        $key = $this->getOriginalLangcode().',';
        if ($parameters->has($key)) {
            return $parameters->get($key)->parameters;
        }

        return [];
    }

    /**
     * 获取所有列和字段值
     *
     * @param  array $keys 限定键名
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        // 尝试从缓存获取数据
        if ($attributes = $this->pipeCache(__FUNCTION__)) {
            $attributes = $attributes->value();
        }

        // 生成属性数组
        else {
            $attributes = array_merge(
                $this->attributesToArray(), $this->getParameters()
            );
            $attributes['delta'] = $this->pivot ? intval($this->pivot->delta) : 0;
            $this->cached[__FUNCTION__] = $attributes;
        }

        if ($keys && !in_array('*', $keys)) {
            $attributes = Arr::only($attributes, $keys);
        }

        return $attributes;
    }

    /**
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public function getFieldType()
    {
        // 尝试从缓存获取数据
        if ($result = $this->pipeCache(__FUNCTION__)) {
            return $result->value();
        }
        return FieldTypeManager::findOrFail($this->attributes['field_type_id'])->bindField($this);
    }

    /**
     * 获取字段值模型
     *
     * @return \App\EntityField\FieldValueBase
     */
    public function getValueModel()
    {
        // 尝试从缓存获取数据
        if ($result = $this->pipeCache(__FUNCTION__)) {
            return $result->value();
        }
        return $this->getFieldType()->getValueModel();
    }

    /**
     * 判断是否使用动态表保存字段值
     *
     * @return bool
     */
    public function hasDynamicValueTable()
    {
        return !$this->getFieldType()->getTable();
    }

    /**
     * 获取存储字段值的数据库表的表名
     *
     * @return string
     */
    public function getValueTable()
    {
        return $this->getFieldType()->getTable() ?: $this->getBoundEntityName().'__'.$this->getKey();
    }

    /**
     * 获取数据表列参数
     *
     * @return array
     */
    public function getValueColumn()
    {
        return $this->getFieldType()->getColumn();
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($value = $this->pipeCache(__FUNCTION__)) {
            return $value->value();
        }
        return $this->getValueModel()->getValue($this->entity);
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value)
    {
        return $this->getValueModel()->setValue($value, $this->entity);
    }

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue()
    {
        return $this->getValueModel()->deleteValue($this->entity);
    }

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle)
    {
        return $this->getValueModel()->searchValue($needle);
    }

    /**
     * 建立字段值存储表
     *
     * @return void
     */
    public function tableUp()
    {
        // 检查是否使用动态表保存字段值，如果不是则不创建
        if (! $this->hasDynamicValueTable()) {
            return;
        }

        // 获取独立表表名，并判断是否已存在
        $tableName = $this->getFieldTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        // 获取用于创建数据表列的参数
        $column = $this->getValueColumn();

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($column) {
            $table->id();
            $table->unsignedBigInteger('entity_id');

            $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);

            $table->string('langcode', 12);
            $table->timestamps();

            $table->unique(['entity_id', 'langcode']);
        });
    }

    /**
     * 删除字段值存储表
     *
     * @return void
     */
    public function tableDown()
    {
        // 检查是否使用动态表保存字段值，如果不是则不删除
        if (! $this->hasDynamicValueTable()) {
            return;
        }
        Schema::dropIfExists($this->getFieldTable());
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function(FieldBase $field) {
            $field->tableUp();
        });

        static::deleted(function(FieldBase $field) {
            $field->tableDown();
        });
    }

    /**
     * @return array[]
     */
    public function getFieldRecords()
    {
        return DB::table($this->getFieldTable())->get()->map(function ($record) {
            return (array) $record;
        })->all();
    }
}
