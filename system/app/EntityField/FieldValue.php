<?php

namespace App\EntityField;

class FieldValue extends FieldValueBase
{
    /**
     * 判断是否使用动态表的模型
     *
     * @return bool
     */
    public static function isDynamic()
    {
        return true;
    }

    /**
     * 绑定到字段
     *
     * @param  \App\EntityField\FieldBase $field
     * @return $this
     */
    public function bindField(FieldBase $field)
    {
        parent::bindField($field);

        // 设置模型表
        $this->setTable($field->getFieldValueTable());

        // 获取列名
        $this->value_column = $this->fieldType->getColumn()['name'];

        // 设置 fillable
        $this->fillable([
            'entity_id',
            $this->value_column => $field->getParameters()['default'] ?? $this->fieldType->getDefaultValue(),
            'langcode',
            'updated_at',
        ]);

        // 设置字段值转换
        $this->casts = [
            $this->value_column => $this->fieldType->getCaster(),
        ];

        return $this;
    }
}
