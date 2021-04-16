<?php

namespace App\EntityField;

use App\Entity\EntityBase;
use App\Entity\Exceptions\InvalidEntityException;
use App\Models\ModelBase;
use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;
use App\Support\Arr;
use App\Support\Types;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class FieldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 主键类型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'field_type',
        'label',
        'description',
        'is_reserved',
        'is_global',
        'field_group',
        'weight',
        'field_meta',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_reserved' => 'bool',
        'is_global' => 'bool',
        'weight' => 'int',
        'field_meta' => Serialized::class
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'delta',
    ];

    /**
     * 字段所属实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $entity;

    /**
     * 字段所属实体
     *
     * @var \App\EntityValue\ValueBase|null
     */
    protected $valueModel = null;

    /**
     * 获取模型模板数据
     *
     * @return array
     */
    public static function template()
    {
        return [
            'id' => null,
            'field_type' => null,
            'label' => null,
            'description' => null,
            'is_reserved' => false,
            'is_global' => false,
            'field_group' => null,
            'weight' => 0,
            'langcode' => langcode('content'),
            'required' => false,
            'default' => null,
            'placeholder' => null,
            'helptext' => null,
            'maxlength' => null,
            'rules' => null,
            'options' => null,
            'reference_scope' => null,
        ];
    }

    /**
     * 获取实体类
     *
     * @return string
     */
    abstract public static function getEntityClass();

    /**
     * 获取翻译类
     *
     * @return string
     */
    abstract public static function getTranslationClass();

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getMoldClass()
    {
        return static::getEntityClass()::getMoldClass();
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotClass()
    {
        return static::getEntityClass()::getPivotClass();
    }

    /**
     * 获取字段绑定实体的实体名
     *
     * @return string
     */
    public static function getBoundEntityName()
    {
        return static::getEntityClass()::getEntityName();
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
        $class = $this->getEntityClass();
        if ($entity instanceof $class) {
            $this->entity = $entity;
            return $this;
        } else {
            throw new InvalidEntityException('字段无法绑定到实体：'.get_class($entity));
        }
    }

    /**
     * 限定可检索字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchable($query)
    {
        return $query->where('weight', '>', 0);
    }

    /**
     * 限定全局字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsGlobal($query)
    {
        return $query->where('is_global', true);
    }

    /**
     * 限定预设字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsReserved($query)
    {
        return $query->where('is_reserved', true);
    }

    /**
     * 限定预设字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsPreseted($query)
    {
        return $query->where('is_global', true)->orWhere('is_reserved', true);
    }

    /**
     * 限定候选字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIsOptional($query)
    {
        return $query->where(['is_global' => false, 'is_reserved' => false]);
    }

    /**
     * 将字段按预设类型分组
     *
     * @return \Illuminate\Support\Collection
     */
    public static function groupbyPresetType()
    {
        return static::all()->groupBy(function(FieldBase $field) {
            if ($field->is_global) {
                return 'global';
            } elseif ($field->is_reserved) {
                return 'reserved';
            } else {
                return 'optional';
            }
        });
    }

    /**
     * 将字段分为预设和非预设两组
     *
     * @return \Illuminate\Support\Collection
     */
    public static function bisect()
    {
        return static::all()->map(function(FieldBase $field){
            return  $field->getMeta();
        })->groupBy(function($field) {
            if ($field['is_global'] || $field['is_reserved']) {
                return 'preseted';
            } else {
                return 'optional';
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        if ($this->entity) {
            return $this->entity->getLangcode();
        }
        return $this->translationLangcode ?? $this->getOriginalLangcode();
    }

    /**
     * label 属性的 Get Mutator
     *
     * @param  string|null $label
     * @return string
     */
    public function getLabelAttribute($label)
    {
        if ($this->pivot) {
            $label = $this->pivot->label;
        }
        return trim($label);
    }

    /**
     * description 属性的 Get Mutator
     *
     * @param  string|null $description
     * @return string
     */
    public function getDescriptionAttribute($description)
    {
        if ($this->pivot) {
            $description = $this->pivot->description;
        }
        return trim($description);
    }

    /**
     * field_meta 属性的 Get Mutator
     *
     * @param  array $field_meta
     * @return array
     */
    public function getFieldMetaAttribute($field_meta)
    {
        if ($this->pivot) {
            $field_meta = $this->pivot->field_meta;
        }

        if (is_string($field_meta)) {
            try {
                $field_meta = unserialize($field_meta);
            } catch (\Throwable $th) {
                //
            }
        }

        return is_array($field_meta) ? $field_meta : [];
    }

    /**
     * delta 属性的 Get Mutator
     *
     * @return int
     */
    public function getDeltaAttribute()
    {
        if ($this->pivot) {
            return $this->pivot->delta;
        }
        return 0;
    }

    /**
     * 获取字段构造元数据
     *
     * @return array|string|null
     */
    public function getMeta(string $key = null)
    {
        if (! isset($this->cachedMeta)) {

            // if ($this->isTranslated() && $translation = static::getTranslationClass()::ofField($this)->first()) {
            //     return $translation->field_meta;
            // }

            $meta = $this->attributesToArray();
            $meta = array_merge($meta, $meta['field_meta'] ?? []);
            unset($meta['field_meta']);

            $this->cachedMeta = $meta;
        }

        if ($key) {
            return $this->cachedMeta[$key] ?? null;
        }

        return $this->cachedMeta;
    }

    /**
     * 获取字段构造元数据
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->getMeta();
    }

    /**
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public function getFieldType()
    {
        return (new $this->attributes['field_type'])->bindField($this);
    }

    /**
     * 生成表单控件
     *
     * @param  mixed $value 字段值
     * @return string
     */
    public function render($value = null)
    {
        return $this->getFieldType()->render($value);
    }

    /**
     * 获取字段值模型
     *
     * @return \App\EntityValue\ValueBase
     */
    public function getValueModel()
    {
        if ($model = $this->cachePipe(__FUNCTION__)) {
            return $model->value();
        }
        return $this->getFieldType()->getValueModel();
    }

    /**
     * 获取字段默认值
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->getMeta('default') ?? $this->getFieldType()->getDefaultValue();
    }

    /**
     * 获取所有字段值
     *
     * @return array
     */
    public function getValues()
    {
        if ($value = $this->cachePipe(__FUNCTION__)) {
            return $value->value();
        }
        return $this->getValueModel()->values();
    }

    /**
     * 获取所有字段值，不区分语言
     *
     * @return array[]
     */
    public function getValueRecords()
    {
        if ($value = $this->cachePipe(__FUNCTION__)) {
            return $value->value();
        }
        return $this->getValueModel()->records();
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if (!$this->valueModel && $this->entity) {
            $model = $this->getValueModel();
            $this->valueModel = $model->getInstance() ?? $model;
        }

        if ($this->valueModel) {
            return $this->valueModel->getValue();
        }

        return null;
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value)
    {
        $value = $this->getFieldType()->formatRecordValue($value);

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
     * 判断是否使用动态表存储
     *
     * @return string
     */
    public function useDynamicValueTable()
    {
        return $this->getValueModel()->isDynamic();
    }

    /**
     * 获取存储字段值的动态数据库表的表名
     *
     * @return string
     */
    public function getDynamicValueTable()
    {
        return $this->getBoundEntityName().'__'.$this->getKey();
    }

    /**
     * 获取存储字段值的数据库表的表名
     *
     * @return string
     */
    public function getValueTable()
    {
        return $this->getValueModel()->getTable();
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
     * 建立字段值存储表
     *
     * @return void
     */
    public function tableUp()
    {
        // 检查是否使用动态表保存字段值，如果不是则不创建
        if (! $this->useDynamicValueTable()) {
            return;
        }

        // 获取独立表表名，并判断是否已存在
        $tableName = $this->getDynamicValueTable();
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
        // 检查是否使用动态表保存字段值，如果不是则不创建
        if (! $this->useDynamicValueTable()) {
            return;
        }
        Schema::dropIfExists($this->getDynamicValueTable());
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

        static::deleting(function(FieldBase $field) {
            $field->tableDown();
        });
    }
}
