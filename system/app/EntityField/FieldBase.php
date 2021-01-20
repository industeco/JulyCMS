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
use App\Model;

abstract class FieldBase extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * 绑定实体的实体名
     *
     * @var string|null
     */
    protected $boundEntityName = null;

    /**
     * 绑定实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $boundEntity;

    /**
     * 绑定实体
     *
     * @var \App\Entity\EntityMoldBase
     */
    protected $boundMold;

    /**
     * 字段类型
     *
     * @var \App\EntityField\FieldTypes\FieldTypeBase
     */
    protected $fieldType = null;

    /**
     * 获取字段绑定实体的实体名
     *
     * @return string|null
     */
    public function getBoundEntityName()
    {
        if ($this->boundEntityName) {
            return $this->boundEntityName;
        } elseif ($this->boundEntity) {
            return $this->boundEntity->getEntityName();
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
        return $this->boundEntity;
    }

    /**
     * 绑定宿主实体
     *
     * @param  \App\Entity\EntityBase $entity
     * @return $this
     *
     * @throws \App\Entity\Exceptions\InvalidEntityException
     */
    public function bindEntity(EntityBase $entity)
    {
        $class = $this->boundEntityName ? EntityManager::resolveName($this->boundEntityName) : null;
        if (!$class || $entity instanceof $class) {
            $this->boundEntity = $entity;
            $this->boundMold = $entity->getMold();
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
        if (! $this->fieldType) {
            $this->fieldType = FieldTypeManager::findOrFail($this->attributes['field_type_id'])->bindField($this);
        }
        return $this->fieldType;
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        if (!$this->contentLangcode && $this->boundEntity) {
            return $this->boundEntity->getLangcode();
        }
        return $this->contentLangcode;
    }

    /**
     * 获取字段参数
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function fieldParameters()
    {
        return $this->morphMany(FieldParameters::class, null, 'entity_name', 'field_id');
    }

    /**
     * 获取字段参数
     *
     * @return array
     */
    public function getParameters()
    {
        // 获取字段的所有相关参数
        $fieldParameters = FieldParameters::query()->where([
            'entity_name' => $this->getBoundEntityName(),
            'field_id' => $this->getKey(),
        ])->get()->keyBy(function(FieldParameters $item) {
            return $item->langcode.','.$item->mold_id;
        });

        // 实体类型 id
        $moldId = $this->boundMold->getKey();

        // 可能的键名（语言 + 实体类型 id），按匹配度降序排列：
        //  - 当前内容语言,当前类型 id
        //  - 实体类型源语言,当前类型 id
        //  - 字段源语言
        $keys = [
            $this->getLangcode().','.$moldId,
            $this->boundMold->getOriginalLangcode().','.$moldId,
            $this->getOriginalLangcode().',',
        ];

        foreach ($keys as $key) {
            if ($parameters = $fieldParameters->get($key)) {
                return $parameters->parameters;
            }
        }

        return [];
    }

    /**
     * 收集实体的常用属性组成数组
     *
     * @param  array $keys 限定的列名
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        $attributes = $this->entityToArray();
        $attributes['delta'] = 0;
        if ($pivot = $this->pivot) {
            $attributes['label'] = $pivot->label ?? $attributes['label'];
            $attributes['description'] = $pivot->description ?? $attributes['description'];
            $attributes['delta'] = (int) $pivot->delta;
        }

        if (in_array('*', $keys) || in_array('parameters', $keys)) {
            $attributes['parameters'] = $this->getParameters();
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
    public function getFieldColumns()
    {
        return $this->getFieldType()->getColumns();
    }

    /**
     * 获取绑定实体在数据表中的外键名
     *
     * @return string
     */
    public function getBoundEntityForeignKey()
    {
        return $this->getBoundEntityName().'_id';
    }

    /**
     * 一组常用量
     *
     * @return array
     */
    protected function sharedVariables()
    {
        return [
            $this->getFieldTable(),
            $this->getBoundEntityForeignKey(),
            $this->boundEntity->getEntityId(),
            $this->boundEntity->getLangcode(),
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
        list($table, $foreignKey, $entityId, $langcode) = $this->sharedVariables();

        // 清除字段值缓存
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        Pocket::make($this, $cachekey)->clear();

        DB::beginTransaction();

        // 删除旧记录
        DB::delete("DELETE FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=?", [$entityId, $langcode]);

        // 插入新记录
        if ($record = $this->getFieldType()->toRecord($value)) {
            DB::table($table)->insert($record + [
                $foreignKey => $entityId,
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
        if ($this->boundEntity && !$this->boundEntity->exists) {
            return $this->getFieldType()->getDefaultValue();
        }

        list($table, $foreignKey, $entityId, $langcode) = $this->sharedVariables();

        // 尝试从缓存获取值
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        $pocket = Pocket::make($this)->useKey($cachekey);
        if ($value = $pocket->get()) {
            return $value->value();
        }

        $value = null;

        // 从数据库获取值
        $record = DB::table($table)->where([$foreignKey=>$entityId, 'langcode'=>$langcode])->first();
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
        list($table, $foreignKey, $entityId, $langcode) = $this->sharedVariables();

        // 清除字段值缓存
        $cachekey = join('/', [$entityId, $langcode, 'value']);
        Pocket::make($this)->useKey($cachekey)->clear();

        DB::delete("DELETE FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=?", [$entityId, $langcode]);
    }

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle)
    {
        $conditions = [];
        foreach ($this->getFieldColumns() as $column) {
            $conditions[] = [$column['name'], 'like', '%'.$needle.'%', 'or'];
        }

        $field = $this->gather(['id', 'field_type_id', 'label', 'description']);

        // 实体在存储表中的外键名
        $foreignKey = $this->getBoundEntityForeignKey();

        $results = [];
        foreach (DB::table($this->getFieldTable())->where($conditions)->get() as $record) {
            $record = (array) $record;
            $key = join('/', [$record[$foreignKey], $field['id'], $record['langcode'] ?? 'und']);
            if (! isset($results[$key])) {
                $results[$key] = $field + [
                    'entity_id' => $record[$foreignKey],
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
        // 获取独立表表名，并判断是否已存在
        $tableName = $this->getFieldTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        // 宿主实体的外键名
        $foreignKey = $this->getBoundEntityForeignKey();

        // 获取用于创建数据表列的参数
        $columns = $this->getFieldColumns();

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($columns, $foreignKey) {
            $table->id();
            $table->unsignedBigInteger($foreignKey);

            foreach($columns as $column) {
                $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
            }

            // $table->unsignedTinyInteger('delta')->default(0);
            $table->string('langcode', 12);
            $table->timestamps();

            $table->unique([$foreignKey, 'langcode', 'delta']);
        });

        // $this->getFieldLinkage()->tableUp();
    }

    /**
     * 移除字段存储表
     *
     * @return void
     */
    public function tableDown()
    {
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

    // public function getValues(string $langcode = null)
    // {
    //     if (is_null($table = $this->tableName())) {
    //         //
    //         return [];
    //     }

    //     if ($langcode) {
    //         return DB::table($table)->where('langcode', $langcode)->get();
    //     } else {
    //         return DB::table($table)->get();
    //     }
    // }

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
