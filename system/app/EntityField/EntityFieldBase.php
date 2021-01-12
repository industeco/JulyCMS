<?php

namespace App\EntityField;

use App\Utils\Pocket;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Entity\EntityBase;
use App\Entity\EntityManager;
use App\EntityField\Exceptions\InvalidHostEntityException;

abstract class EntityFieldBase extends EntityBase
{
    /**
     * 宿主实体的实体名
     *
     * @var string|null
     */
    protected static $hostEntityName;

    /**
     * 字段宿主实体
     *
     * @var \App\Entity\EntityBase
     */
    protected $hostEntity;

    /**
     * 获取字段宿主实体的实体名
     *
     * @return string
     */
    public static function getHostEntityName()
    {
        return static::$hostEntityName ?: preg_replace('/_field$/', '', static::getEntityName());
    }

    /**
     * 获取绑定的实体
     *
     * @return \App\Entity\EntityBase
     *
     * @throws \App\EntityField\Exceptions\InvalidHostEntityException
     */
    public function getHostEntity()
    {
        if ($this->hostEntity) {
            return $this->hostEntity;
        }

        if ($class = EntityManager::resolveName(static::getHostEntityName())) {
            return $this->hostEntity = new $class;
        }

        throw new InvalidHostEntityException;
    }

    /**
     * 绑定宿主实体
     *
     * @param  \App\Entity\EntityBase $entity
     * @return $this
     *
     * @throws \App\EntityField\Exceptions\InvalidHostEntityException
     */
    public function bindEntity(EntityBase $entity)
    {
        $class = EntityManager::resolveName(static::getHostEntityName());
        if ($class && $entity instanceof $class) {
            $this->hostEntity = $entity;
            return $this;
        }

        throw new InvalidHostEntityException;
    }

    /**
     * 获取字段类型对象
     *
     * @return \App\EntityField\FieldType
     */
    public function getFieldType()
    {
        return FieldType::findOrFail($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getLangcode()
    {
        $host = $this->getHostEntity();
        if ($host->getLangcode()) {
            $this->contentLangcode = $host->getLangcode();
        }

        return $this->contentLangcode ?: langcode('content');
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
        // 实体类型 id
        $bundleName = null;
        if ($pivot = $this->pivot) {
            $bundleName = $pivot->{$pivot->getForeignKey()};
        }

        // 语言版本
        $langcode = $this->getLangcode();

        // 可能的键名（语言版本 + 实体类型名），按匹配度降序排列
        $keys = [
            $langcode.'|'.$bundleName,
            $langcode.'|',
        ];

        /**
         * 获取当前字段相关的所有参数
         *
         * @var \Illuminate\Database\Eloquent\Collection
         */
        $fieldParameters = $this->fieldParameters->keyBy(function(FieldParameters $item) {
            return $item->langcode.'|'.$item->bundle_name;
        });

        // return $fieldParameters;

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

        if ($keys && !in_array('*', $keys)) {
            $attributes = Arr::only($attributes, $keys);
        }

        if (in_array('*', $keys) || in_array('parameters', $keys)) {
            $attributes['parameters'] = $this->getParameters();
        }

        return $attributes;
    }

    /**
     * 获取存储字段值的数据库表的表名
     *
     * @return string
     */
    public function getValueTable()
    {
        return static::getHostEntityName().'__'.$this->getEntityId();
    }

    /**
     * 获取数据表列参数
     *
     * @return array
     */
    public function getValueColumns()
    {
        return $this->getFieldType()->getColumns();
    }

    /**
     * 获取绑定实体在数据表中的外键名
     *
     * @return string
     */
    public function getHostForeignKey()
    {
        return static::getHostEntityName().'_id';
    }

    /**
     * 一组常用量
     *
     * @return array
     *
     * @throws \App\EntityField\Exceptions\InvalidHostEntityException
     */
    protected function sharedVariables()
    {
        try {
            return [
                $this->getValueTable(),
                $this->getHostForeignKey(),
                $this->hostEntity->getEntityId(),
                $this->hostEntity->getLangcode(),
            ];
        } catch (\Throwable $th) {
            throw new InvalidHostEntityException($th->getMessage());
        }
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
        Pocket::make($this)->useKey($cachekey)->clear();

        DB::beginTransaction();

        // 删除旧记录
        DB::delete("DELETE FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=?", [$entityId, $langcode]);

        // 插入新记录
        if ($records = $this->getFieldType()->toRecords($value)) {
            foreach ($records as $index => $record) {
                DB::table($table)->insert($record + [
                    $foreignKey => $entityId,
                    'langcode' => $langcode,
                    'delta' => $index,
                ]);
            }
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
        if ($this->hostEntity && !$this->hostEntity->exists) {
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
        $records = DB::select("SELECT * FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=? ORDER BY `delta`", [$entityId, $langcode]);
        if (! empty($records)) {
            // 借助字段类型，将数据库记录重新组合为字段值
            $value = $this->getFieldType()->toValue(array_map(function($record) {
                return (array) $record;
            }, $records));
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
        foreach ($this->getValueColumns() as $column) {
            $conditions[] = [$column['name'], 'like', '%'.$needle.'%', 'or'];
        }

        $field = $this->gather(['id', 'field_type_id', 'label', 'description']);

        // 实体在存储表中的外键名
        $foreignKey = $this->getHostForeignKey();

        $results = [];
        foreach (DB::table($this->getValueTable())->where($conditions)->get() as $record) {
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
        $tableName = $this->getValueTable();
        if (Schema::hasTable($tableName)) {
            return;
        }

        // 宿主实体的外键名
        $foreignKey = $this->getHostForeignKey();

        // 获取用于创建数据表列的参数
        $columns = $this->getValueColumns();

        // 创建数据表
        Schema::create($tableName, function (Blueprint $table) use ($columns, $foreignKey) {
            $table->id();
            $table->unsignedBigInteger($foreignKey);

            foreach($columns as $column) {
                $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
            }

            $table->unsignedTinyInteger('delta')->default(0);
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
        Schema::dropIfExists($this->getValueTable());
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function(EntityFieldBase $field) {
            $field->tableUp();
        });

        static::deleted(function(EntityFieldBase $field) {
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
    public function getValueRecords()
    {
        return DB::table($this->getValueTable())->get()->map(function ($record) {
            return (array) $record;
        })->all();
    }
}
