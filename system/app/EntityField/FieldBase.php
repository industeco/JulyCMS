<?php

namespace App\EntityField;

use App\EntityField\FieldTypes\FieldTypeManager;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\Entity\Exceptions\InvalidEntityException;
use App\Models\ModelBase;
use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;
use App\Utils\Arr;
use App\Utils\Pocket;
use App\Utils\Types;
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
        'field_type_id',
        'is_reserved',
        'is_global',
        'group_title',
        'search_weight',
        'maxlength',
        'label',
        'description',
        'is_required',
        'helpertext',
        'default_value',
        'options',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_reserved' => 'boolean',
        'is_global' => 'boolean',
        'search_weight' => 'int',
        'maxlength' => 'int',
        'is_required' => 'boolean',
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
     * 获取模型模板数据
     *
     * @return array
     */
    public static function template()
    {
        return [
            'id' => null,
            'field_type_id' => null,
            'is_reserved' => false,
            'is_global' => false,
            'group_title' => null,
            'search_weight' => 0,
            'maxlength' => 0,
            'label' => null,
            'description' => null,
            'is_required' => false,
            'helpertext' => null,
            'default_value' => null,
            'options' => [],
            'langcode' => langcode('content'),
        ];
    }

    /**
     * 获取实体类
     *
     * @return string
     */
    abstract public static function getEntityModel();

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getMoldModel()
    {
        return static::getEntityModel()::getMoldModel();
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotModel()
    {
        return static::getEntityModel()::getPivotModel();
    }

    /**
     * 获取字段绑定实体的实体名
     *
     * @return string
     */
    public static function getBoundEntityName()
    {
        return static::getEntityModel()::getEntityName();
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
        $class = $this->getEntityModel();
        if ($entity instanceof $class) {
            $this->entity = $entity;
            return $this;
        } else {
            throw new InvalidEntityException('当前字段无法绑定到实体：'.get_class($entity));
        }
    }

    /**
     * 将字段分为预设和可选
     *
     * @return array
     */
    public static function classify()
    {
        $optional = [];
        $preseted = [];
        foreach (static::all() as $field) {
            $field = $field->attributesToArray();
            if ($field['is_reserved'] || $field['is_global']) {
                $preseted[$field['id']] = $field;
            } else {
                $optional[$field['id']] = $field;
            }
        }
        $fields = compact('optional', 'preseted');

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        if ($this->entity) {
            return $this->entity->getLangcode();
        }
        return $this->contentLangcode ?? $this->getOriginalLangcode();
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
     * is_required 属性的 Get Mutator
     *
     * @param  bool|int $required
     * @return bool
     */
    public function getIsRequiredAttribute($required)
    {
        if ($this->pivot) {
            $required = $this->pivot->is_required;
        }
        return (bool) $required;
    }

    /**
     * helpertext 属性的 Get Mutator
     *
     * @param  string|null $helpertext
     * @return string
     */
    public function getHelpertextAttribute($helpertext)
    {
        if ($this->pivot) {
            $helpertext = $this->pivot->helpertext;
        }
        return trim($helpertext);
    }

    /**
     * default_value 属性的 Get Mutator
     *
     * @param  string|null $defaultValue
     * @return string
     */
    public function getDefaultValueAttribute($defaultValue)
    {
        if ($this->pivot) {
            $defaultValue = $this->pivot->default_value;
        }
        return Types::cast($defaultValue, $this->getFieldType()->getCaster());
    }

    /**
     * options 属性的 Get Mutator
     *
     * @param  string|null $options
     * @return string
     */
    public function getOptionsAttribute($options)
    {
        if ($this->pivot) {
            $options = $this->pivot->options;
        }
        return trim($options);
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
     * 获取所有列和字段值
     *
     * @param  array $keys 限定键名
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        // 尝试从缓存获取数据
        if ($attributes = $this->cachePipe(__FUNCTION__)) {
            $attributes = $attributes->value();
        }

        // 生成属性数组
        else {
            $attributes = $this->attributesToArray();
        }

        if ($keys && $keys !== ['*']) {
            $attributes = Arr::only($attributes, $keys);
        }

        return $attributes;
    }

    /**
     * 获取字段参数
     *
     * @return array
     */
    public function getParameters()
    {
        // 尝试从缓存获取数据
        if ($result = $this->cachePipe(__FUNCTION__)) {
            return $result->value();
        }

        $parameters = null;

        // 获取翻译过的模型字段参数
        if ($this->entity && $this->entity->getMold()->isTranslated()) {
            $parameters = FieldParameters::ofField($this)->where('mold_id', $this->entity->mold_id)->first();
        }

        // 获取翻译过的字段参数
        elseif (!$this->entity && $this->isTranslated()) {
            $parameters = FieldParameters::ofField($this)->where('mold_id', null)->first();
        }

        if ($parameters) {
            return [
                'default_value' => $parameters->default_value,
                'options' => $parameters->options,
            ];
        }

        return [];
    }

    /**
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public function getFieldType()
    {
        // 尝试从缓存获取数据
        if ($result = $this->cachePipe(__FUNCTION__)) {
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
        if ($model = $this->cachePipe(__FUNCTION__)) {
            return $model->value();
        }
        return $this->getFieldType()->getValueModel();
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($value = $this->cachePipe(__FUNCTION__)) {
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

    /**
     * @return array[]
     */
    public function getValueRecords()
    {
        return DB::table($this->getValueTable())->get()->map(function ($record) {
            return (array) $record;
        })->all();
    }
}
