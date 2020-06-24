<?php

namespace App\Models;

use App\Contracts\Entity;
use App\Support\Arr;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class BaseFieldEntity extends JulyModel implements Entity
{
    /**
     * 获取字段参数
     *
     * @param string|null $langcode
     * @return array
     */
    public function getParameters($langcode = null)
    {
        $entity = str_replace('.', '_', static::getParentEntityId());
        $original_lang = $this->getAttribute('langcode');
        $langcode = $langcode ?? $original_lang;

        $keyname = implode('.', [$entity.'_field', $this->getKey()]);
        $records = FieldParameters::where('keyname', 'like', $keyname.'.%')->get()->pluck('data', 'keyname');

        $parameters = $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang];
        if ($pivot = $this->pivot) {
            $keyname = implode('.', [$keyname, $entity.'_type', $pivot->getAttribute($entity.'_type')]);
            $parameters = array_merge(
                    $parameters,
                    $records[$keyname.'.'.$langcode] ?? $records[$keyname.'.'.$original_lang] ?? []
                );
        }

        return $parameters;
    }

    public function tableName()
    {
        return str_replace('.', '_', static::getParentEntityId()) . '__' . $this->getKey();
    }

    public function tableColumns()
    {
        return $this->fieldType()->getColumns();
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
            $columnId = str_replace('.', '_', static::getParentEntityId()).'_id';

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

    /**
     * 删除字段值
     */
    public function deleteValue($id, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        $idName = str_replace('.', '_', static::getParentEntityId()).'_id';

        // 清除字段值缓存
        $this->cacheClear($this->cacheKey('values', [
            $idName => $id,
            'langcode' => $langcode,
        ]));

        $table = $this->tableName();
        DB::delete("DELETE FROM `$table` WHERE `{$idName}`=? AND `langcode`=?", [$id, $langcode]);

        return true;
    }

    /**
     * 设置字段值
     *
     * @param int $content_id
     * @param mixed $value
     * @return void
     */
    public function setValue($value, $content_id, $langcode = null)
    {
        // Log::info("Updating field '{$this->truename}'");

        $langcode = $langcode ?: langcode('content');
        // Log::info("langcode: '{$langcode}'");

        // 清除字段值缓存
        $this->cacheClear($this->cacheKey('values', compact('content_id', 'langcode')));

        $records = $this->fieldType()->toRecords($value);
        if (is_null($records)) {
            $this->deleteValue($content_id, $langcode);
        } else {
            foreach ($records as $index => &$record) {
                $record['content_id'] = $content_id;
                $record['langcode'] = $langcode;
                $record['delta'] = $index;
            }
            unset($record);

            $table = $this->tableName();
            // Log::info("table: '{$table}'");

            DB::beginTransaction();
            DB::delete("DELETE FROM `$table` WHERE `content_id`=? AND `langcode`=?", [$content_id, $langcode]);
            foreach ($records as $record) {
                DB::table($table)->insert($record);
            }
            DB::commit();
        }
    }

    /**
     * 获取字段值
     *
     * @param \App\Models\Content $content
     * @param string $langcode 语言代码
     * @return mixed
     */
    public function getValue(Content $content, $langcode = null)
    {
        $langcode = $langcode ?: $content->getAttribute('langcode');

        $cacheKey = $this->cacheKey('values', [
            'content_id' => $content->getKey(),
            'langcode' => $langcode,
        ]);

        if ($value = $this->cacheGet($cacheKey)) {
            return $value['value'];
        }

        $value = null;
        $records = DB::table($this->tableName())->where([
            ['content_id', $content->getKey()],
            ['langcode', $langcode],
        ])->orderBy('delta')->get();

        if ($records->count()) {
            $records = $records->map(function($record) {
                return (array) $record;
            })->all();

            // 借助字段类型格式化数据库记录
            $value = $this->fieldType()->toValue($records);
        }

        // 缓存字段值
        $this->cachePut($cacheKey, $value);

        return $value;
    }
}
