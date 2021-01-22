<?php

namespace App\EntityField;

use App\Utils\Pocket;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\Entity\Exceptions\InvalidEntityException;
use App\EntityField\Exceptions\InvalidBoundEntityException;
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
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldTypes\FieldTypeBase
     */
    public function getFieldType()
    {
        // 尝试从缓存获取数据
        $cacheKey = 'field_type';
        if (isset($this->cached[$cacheKey])) {
            return $this->cached[$cacheKey];
        }

        return $this->cached[$cacheKey] = FieldTypeManager::findOrFail($this->attributes['field_type_id'])->bindField($this);
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
        if ($parameters = $this->cacheGet(__FUNCTION__)) {
            return $parameters->value();
        }

        // 获取字段的所有相关参数
        /** @var \Illuminate\Database\Eloquent\Collection */
        $fieldParameters = FieldParameters::ofField($this)->get()
            ->keyBy(function($item) {
                return $item->langcode.','.$item->mold_id;
            });

        // 实体类型
        $mold = $this->entity->getMold();

        // 可能的键名（语言 + 实体类型 id），按匹配度降序排列：
        //  - 当前内容语言，当前类型 id
        //  - 实体类型源语言，当前类型 id
        //  - 字段源语言
        $keys = [
            $this->getLangcode().','.$mold->getKey(),
            $mold->getOriginalLangcode().','.$mold->getKey(),
            $this->getOriginalLangcode().',',
        ];

        foreach ($keys as $key) {
            if ($fieldParameters->has($key)) {
                return $fieldParameters->get($key)->parameters;
            }
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
        if ($attributes = $this->cacheGet(__FUNCTION__)) {
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
     * 获取存储字段值的数据库表的表名
     *
     * @return string
     */
    public function getFieldTable()
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
     * 一组常用量
     *
     * @return array
     */
    protected function sharedVariables()
    {
        if (isset($this->cached[__FUNCTION__])) {
            return $this->cached[__FUNCTION__];
        }

        return $this->cached[__FUNCTION__] = [
            $this->getFieldTable(),
            $this->entity->getEntityId(),
            $this->entity->getLangcode(),
        ];
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     */
    public function setValue($value)
    {
        list($table, $entityId, $langcode) = $this->sharedVariables();

        // 清除字段值缓存
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        Pocket::make($this, $cachekey)->clear();

        DB::beginTransaction();

        // 删除旧记录
        DB::delete("DELETE FROM `{$table}` WHERE `entity_id`=? AND `langcode`=?", [$entityId, $langcode]);

        // 插入新记录
        if ($record = $this->getFieldType()->toRecord($value)) {
            DB::table($table)->insert($record + [
                'entity_id' => $entityId,
                'langcode' => $langcode,
            ]);
        }

        DB::commit();
    }

    /**
     * 获取字段值
     *
     * @return mixed
     */
    public function getValue()
    {
        if ($this->entity && !$this->entity->exists) {
            return $this->getParameters()['default'] ?? $this->getFieldType()->getDefaultValue();
        }

        list($table, $entityId, $langcode) = $this->sharedVariables();

        // 尝试从缓存获取值
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        $pocket = Pocket::make($this)->setKey($cachekey);
        if ($value = $pocket->get()) {
            return $value->value();
        }

        $value = null;

        // 从数据库获取值
        $record = DB::table($table)->where(['entity_id'=>$entityId, 'langcode'=>$langcode])->first();
        if ($record) {
            // 借助字段类型，将数据库记录重新组合为字段值
            $value = $this->getFieldType()->toValue((array) $record);
        }

        // 缓存字段值
        $pocket->put($value);

        return $value;
    }

    /**
     * 删除字段值
     *
     * @return void
     */
    public function deleteValue()
    {
        list($table, $entityId, $langcode) = $this->sharedVariables();

        // 清除字段值缓存
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        Pocket::make($this, $cachekey)->clear();

        DB::delete("DELETE FROM `{$table}` WHERE `entity_id`=? AND `langcode`=?", [$entityId, $langcode]);
    }

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle)
    {
        $column = $this->getValueColumn();
        $conditions = [$column['name'], 'like', '%'.$needle.'%', 'or'];

        $field = $this->gather(['id', 'field_type_id', 'label', 'description']);

        $results = [];
        foreach (DB::table($this->getFieldTable())->where($conditions)->get() as $record) {
            $record = (array) $record;
            $key = join('/', [$record['entity_id'], $field['id'], $record['langcode'] ?? 'und']);
            if (! isset($results[$key])) {
                $results[$key] = $field + [
                    'entity_id' => $record['entity_id'],
                    'langcode' => $record['langcode'] ?? 'und',
                ];
            }
        }

        return array_values($results);
    }

    /**
     * 建立字段存储表
     *
     * @return void
     */
    public function tableUp()
    {
        // 检查存储表是否由字段类型提供
        // 如果是，则不创建
        if ($this->getFieldType()->getTable()) {
            return;
        }

        // 获取独立表表名，并判断是否已存在
        $tableName = $this->getFieldTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        // 获取用于创建数据表列的参数
        $columns = $this->getValueColumn();

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($columns) {
            $table->id();
            $table->unsignedBigInteger('entity_id');

            foreach($columns as $column) {
                $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
            }

            $table->string('langcode', 12);
            $table->timestamps();

            $table->unique(['entity_id', 'langcode']);
        });
    }

    /**
     * 删除字段存储表
     *
     * @return void
     */
    public function tableDown()
    {
        // 检查存储表是否由字段类型提供
        // 如果是，则不删除
        if (! $this->getFieldType()->getTable()) {
            Schema::dropIfExists($this->getFieldTable());
        }
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
