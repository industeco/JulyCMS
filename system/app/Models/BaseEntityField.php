<?php

namespace App\Models;

use App\Contracts\Entity;
use App\EntityFieldTypes\EntityFieldType;
use App\Support\Arr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class BaseEntityField extends BaseModel implements Entity
{
    /**
     * 获取字段类型对象
     *
     * @param string|null $langcode
     * @return \App\EntityFieldTypes\EntityFieldTypeInterface
     */
    public function eaType($langcode = null)
    {
        return EntityFieldType::find($this->getAttribute('field_type'), $this, $langcode);
    }

    /**
     * 获取字段参数
     *
     * @param string|null $langcode
     * @return array
     */
    public function getParameters($langcode = null)
    {
        $original_lang = $this->getAttribute('langcode');
        $langcode = $langcode ?? $original_lang;

        $keyname = implode('.', [static::getEntityId(), $this->getKey()]);
        $records = FieldParameters::where('keyname', 'like', $keyname.'.%')->get()->pluck('data', 'keyname');

        $parameters = $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang];
        if ($pivot = $this->pivot) {
            $typeEntity = static::getParentEntityId().'_type';
            $keyname = implode('.', [$keyname, $typeEntity, $pivot->getAttribute($typeEntity)]);
            $parameters = array_merge(
                    $parameters,
                    $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang] ?? []
                );
        }

        return $parameters;
    }

    /**
     * 收集字段所有相关信息并组成数组
     *
     * @param string|null $langcode
     * @return array
     */
    public function gather($langcode = null)
    {
        $data = $this->attributesToArray();
        if ($pivot = $this->pivot) {
            $data['label'] = $pivot->label ?? $data['label'];
            $data['description'] = $pivot->description ?? $data['description'];
            $data['delta'] = $pivot->delta;
        }
        $data['parameters'] = $this->getParameters($langcode);

        return $data;
    }

    /**
     * 删除字段值
     */
    public function deleteValue($id, $langcode = null)
    {
        $columnId = static::getParentEntityId().'_id';
        $langcode = $langcode ?: $this->langcode();

        // 清除字段值缓存
        $this->cacheClear([
            'key' => 'values',
            $columnId => $id,
            'langcode' => $langcode,
        ]);

        $table = $this->tableName();
        DB::delete("DELETE FROM `$table` WHERE `{$columnId}`=? AND `langcode`=?", [$id, $langcode]);

        return true;
    }

    /**
     * 设置字段值
     *
     * @param int $id
     * @param mixed $value
     * @return void
     */
    public function setValue($value, $id, $langcode = null)
    {
        $columnId = static::getParentEntityId().'_id';
        $langcode = $langcode ?: $this->langcode();
        // Log::info("langcode: '{$langcode}'");

        // 清除字段值缓存
        $this->cacheClear([
            'key' => 'values',
            $columnId => $id,
            'langcode' => $langcode,
        ]);

        $records = $this->eaType()->toRecords($value);
        if (is_null($records)) {
            $this->deleteValue($id, $langcode);
        } else {
            foreach ($records as $index => &$record) {
                $record[$columnId] = $id;
                $record['langcode'] = $langcode;
                $record['delta'] = $index;
            }
            unset($record);

            $table = $this->tableName();
            // Log::info("table: '{$table}'");

            DB::beginTransaction();
            DB::delete("DELETE FROM `$table` WHERE `{$columnId}`=? AND `langcode`=?", [$id, $langcode]);
            foreach ($records as $record) {
                DB::table($table)->insert($record);
            }
            DB::commit();
        }
    }

    /**
     * 获取字段值
     *
     * @param int $id 内容 id
     * @param string|null $langcode 语言代码
     * @return mixed
     */
    public function getValue($id, $langcode = null)
    {
        $columnId = static::getParentEntityId().'_id';
        $langcode = $langcode ?: $this->langcode();

        $cacheKey = $this->cacheKey( [
            'key' => 'values',
            $columnId => $id,
            'langcode' => $langcode,
        ]);

        if ($value = $this->cacheGet($cacheKey)) {
            return $value['value'];
        }

        $value = null;
        $records = DB::table($this->tableName())->where([
            $columnId => $id,
            'langcode' => $langcode,
        ])->orderBy('delta')->get();

        if ($records->count()) {
            $records = $records->map(function($record) {
                return (array) $record;
            })->all();

            // 借助字段类型格式化数据库记录
            $value = $this->eaType()->toValue($records);
        }

        // 缓存字段值
        $this->cachePut($cacheKey, $value);

        return $value;
    }

    public function getRecords($langcode = null)
    {
        $table = $this->tableName();
        if ($langcode) {
            return DB::table($table)->where('langcode', $langcode)->get();
        } else {
            return DB::table($table)->get();
        }
    }

    public function search($keywords, $langcode = null)
    {
        $keywords = '%'.$keywords.'%';
        $table = $this->tableName();

        $conditions = [];
        foreach ($this->tableColumns() as $column) {
            $conditions[] = [$column['name'], 'like', $keywords, 'or'];
        }

        if ($langcode) {
            $records = DB::table($table)->where($conditions)->where('langcode', $langcode)->get();
        } else {
            $records = DB::table($table)->where($conditions)->get();
        }

        $entity = static::getParentEntityId();
        $columnId = $entity.'_id';

        $fieldName = $this->getKey();
        $EAType = $this->getAttribute('field_type');
        $fieldLabel = $this->getAttribute('label');
        $results = [];
        foreach ($records as $record) {
            $key = implode('/', [$record->{$columnId}, $fieldName, $record->langcode]);
            if (! isset($results[$key])) {
                $results[$key] = [
                    $columnId => $record->{$columnId},
                    $entity.'_field' => $fieldName,
                    'field_type' => $EAType,
                    'field_label' => $fieldLabel,
                    'langcode' => $record->langcode,
                ];
            }
        }

        return array_values($results);
    }

    public function tableName()
    {
        return static::getParentEntityId().'__'.$this->getKey();
    }

    public function tableColumns()
    {
        return $this->eaType()->getColumns();
    }

    /**
     * 建立字段对应的数据表，用于存储字段值
     *
     * @return void
     */
    public function tableUp()
    {
        // 数据表表名
        $tableName = $this->tableName();

        if (! Schema::hasTable($tableName)) {

            // Log::info('TableUp: ' . $tableName);
            $columnId = static::getParentEntityId().'_id';

            // 获取用于创建数据表列的参数
            $columns = $this->tableColumns();
            // Log::info($columns);

            // 创建数据表
            Schema::create($tableName, function (Blueprint $table) use ($columns, $columnId) {
                $table->id();
                $table->unsignedBigInteger($columnId);

                foreach($columns as $column) {
                    $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
                }

                $table->unsignedTinyInteger('delta')->default(0);
                $table->string('langcode', 12);
                $table->timestamps();

                $table->unique([$columnId, 'langcode', 'delta']);
            });
        }
    }

    /**
     * 删除字段对应数据表
     *
     * @return void
     */
    public function tableDown()
    {
        Schema::dropIfExists($this->tableName());
    }

    public static function boot()
    {
        parent::boot();

        static::created(function(BaseEntityField $field) {
            $field->tableUp();
        });

        static::deleted(function(BaseEntityField $field) {
            $field->tableDown();
        });
    }
}
