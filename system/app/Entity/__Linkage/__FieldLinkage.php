<?php

namespace App\Entity\Linkage;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Entity\Exceptions\InvalidEntityException;

// class FieldLinkage extends LinkageBase
// {
//     /**
//      * {@inheritdoc}
//      */
//     public function get()
//     {
//         list($table, $langcode, $foreignKey, $entityId) = $this->getVariables();

//         $records = DB::select("SELECT * FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=? ORDER BY `delta`", [$entityId, $langcode]);

//         if (! empty($records)) {
//             // 借助字段类型，将数据库记录重新组合为字段值
//             return $this->field->getFieldType()->toValue(array_map(function($record) {
//                 return (array) $record;
//             }, $records));
//         }

//         return null;
//     }

//     /**
//      * {@inheritdoc}
//      */
//     public function set($value)
//     {
//         list($table, $langcode, $foreignKey, $entityId) = $this->getVariables();

//         DB::beginTransaction();

//         // 删除旧记录
//         DB::delete("DELETE FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=?", [$entityId, $langcode]);

//         // 插入新记录
//         if ($records = $this->field->getFieldType()->toRecords($value)) {
//             foreach ($records as $index => $record) {
//                 DB::table($table)->insert($record + [
//                     $foreignKey => $entityId,
//                     'langcode' => $langcode,
//                     'delta' => $index,
//                 ]);
//             }
//         }

//         DB::commit();
//     }

//     /**
//      * {@inheritdoc}
//      */
//     public function delete()
//     {
//         list($table, $langcode, $foreignKey, $entityId) = $this->getVariables();

//         DB::delete("DELETE FROM `{$table}` WHERE `{$foreignKey}`=? AND `langcode`=?", [$entityId, $langcode]);
//     }

//     /**
//      * 获取常用的四个变量
//      *
//      * @return array
//      *
//      * @throws \App\Entity\Exceptions\InvalidEntityException
//      */
//     protected function getVariables()
//     {
//         if (! $this->entity->exists) {
//             throw new InvalidEntityException('字段存取器的关联实体无效');
//         }

//         return [
//             $this->getTableName(),
//             $this->entity->getLangcode(),
//             $this->getEntityForeignKeyName(),
//             $this->entity->getEntityId(),
//         ];
//     }

//     /**
//      * {@inheritdoc}
//      */
//     public function tableUp()
//     {
//         // 独立表表名
//         $tableName = $this->getTableName();
//         if (Schema::hasTable($tableName)) {
//             return;
//         }

//         // 实体在存储表中的外键键名
//         $foreignKey = $this->getEntityForeignKeyName();

//         // 获取用于创建数据表列的参数
//         $columns = $this->getTableColumns();

//         // 创建数据表
//         Schema::create($tableName, function (Blueprint $table) use($columns, $foreignKey) {
//             $table->id();
//             $table->unsignedBigInteger($foreignKey);

//             foreach($columns as $column) {
//                 $table->addColumn($column['type'], $column['name'], $column['parameters'] ?? []);
//             }

//             $table->unsignedTinyInteger('delta')->default(0);
//             $table->string('langcode', 12);
//             $table->timestamps();

//             $table->unique([$foreignKey, 'langcode', 'delta']);
//         });
//     }

//     /**
//      * {@inheritdoc}
//      */
//     public function tableDown()
//     {
//         Schema::dropIfExists($this->getTableName());
//     }

//     /**
//      * {@inheritdoc}
//      */
//     public function search(string $needle)
//     {
//         $conditions = [];
//         foreach ($this->getTableColumns() as $column) {
//             $conditions[] = [$column['name'], 'like', '%'.$needle.'%', 'or'];
//         }

//         $fieldInfo = Arr::only($this->field->attributesToArray(), [
//             'id', 'field_type_id', 'label', 'description'
//         ]);

//         // 实体在存储表中的外键名
//         $foreignKey = $this->getEntityForeignKeyName();

//         $results = [];
//         foreach (DB::table($this->getTableName())->where($conditions)->get() as $record) {
//             $record = (array) $record;
//             $key = join('/', [$record[$foreignKey], $fieldInfo['id'], $record['langcode'] ?? 'und']);
//             if (! isset($results[$key])) {
//                 $results[$key] = $fieldInfo + [
//                     'entity_id' => $record[$foreignKey],
//                     'langcode' => $record['langcode'] ?? 'und',
//                 ];
//             }
//         }

//         return array_values($results);
//     }

//     /**
//      * 获取存储字段值的数据库表的表名
//      *
//      * @return string
//      */
//     public function getTableName()
//     {
//         return $this->entity->getEntityName().'__'.$this->field->getKey();
//     }

//     /**
//      * 获取数据表列参数
//      *
//      * @return array
//      */
//     public function getTableColumns()
//     {
//         return $this->field->getFieldType()->getColumns();
//     }

//     /**
//      * 获取存储表中实体的外键名
//      *
//      * @return string
//      */
//     public function getEntityForeignKeyName()
//     {
//         return $this->entity->getEntityName().'_id';
//     }
// }
