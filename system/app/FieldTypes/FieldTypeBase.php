<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\Log;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class FieldTypeBase
{
    public static $isPublic = true;

    public static $label = '';

    public static $description = '';

    public static $searchable = true;

    /**
     * 字段数据存储表的列信息，结构为：
     * [
     *   [
     *      type => string,
     *      name => string,
     *      parameters => array,
     *   ],
     *   ...
     * ]
     *
     * @param string $fieldName
     * @param array $parameters
     * @return array
     */
    abstract public function getColumns($fieldName, array $parameters = []): array;

    /**
     * 获取该类型字段参数的模式数据（属性，属性值类型，属性默认值）
     *
     * @return array
     */
    abstract public function getSchema(): array;

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 表单数据
     * @return array
     */
    abstract public function extractParameters(array $raw): array;

    /**
     * 将记录转换为值
     */
    public function toValue(array $records, array $columns, array $parameters = [])
    {
        $column = $columns[0]['name'];
        return trim($records[0][$column]);
    }

    /**
     * 将值转换为记录
     */
    public function toRecords($value, array $columns)
    {
        if (!strlen($value)) {
            return null;
        }

        return [
            [
                $columns[0]['name'] => $value,
            ],
        ];
    }

    /**
     * 生成表单拼图（用来拼合表单的字段相关数据）
     *
     * @param array $data
     * @return array
     */
    public function getJigsaws(array $fieldData)
    {
        return [
            'truename' => $fieldData['truename'],
            'value' => null,
            'element' => $this->getElement($fieldData),
            'rules' => $this->getRules($fieldData['parameters'] ?? []),
        ];
    }

    /**
     * 获取字段的 HTML 片段（element-ui 组件）
     */
    abstract public function getElement(array $fieldData);

    public function getRules(array $parameters)
    {
        $rules = [];

        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }

        $max = $parameters['max'] ?? 0;
        if ($max > 0) {
            $rules[] = "{max:{$max}, message: '最多 {$max} 个字符', trigger: 'change'}";
        }

        return $rules;
    }
}
