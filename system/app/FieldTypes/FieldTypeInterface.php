<?php

namespace App\FieldTypes;

use App\Models\ContentField;

interface FieldTypeInterface
{
    /**
     * 获取类型别名
     *
     * @return string
     */
    public static function getAlias(): string;

    /**
     * 获取类型标签
     *
     * @return string
     */
    public static function getLabel(): string;

    /**
     * 获取类型描述
     *
     * @return string
     */
    public static function getDescription(): ?string;

    /**
     * 设置字段对象
     *
     * @param array $field
     * @return static
     */
    public function setField(ContentField $field);

    /**
     * 设置字段参数读取语言
     *
     * @param array $langcode
     * @return static
     */
    public function setLangcode($langcode);

    /**
     * 获取该类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @return array
     */
    public function getSchema(): array;

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 表单数据
     * @return array
     */
    public function extractParameters(array $raw): array;

    /**
     * 字段数据存储表的列信息，结构：
     * [[
     *     type => string,
     *     name => string,
     *     parameters => array,
     *   ], ...]
     *
     * @param array|null $parameters
     * @param string|null $fieldName
     * @return array
     */
    public function getColumns($fieldName = null, array $parameters = null): array;

    /**
     * 生成表单拼图，包括 HTML 片段和前端验证规则
     *
     * @param array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return array
     */
    public function getJigsaws(array $data = null): array;

    /**
     * 获取字段的 HTML 片段（element-ui 组件）
     *
     * @param array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return string
     */
    public function getElement(array $data = null): ?string;

    /**
     * 获取前端验证规则
     *
     * @param array|null $parameters 字段参数
     * @return array
     */
    public function getRules(array $parameters = null): array;

    /**
     * 获取后端验证规则
     *
     * @param array|null $parameters 字段参数
     * @return array
     */
    public function getValidator(array $parameters = null): array;

    /**
     * 将记录转换为值
     *
     * @param array $records 表记录
     * @param array $columns|null 字段值表列
     * @param array $parameters|null 字段参数
     * @return mixed
     */
    public function toValue(array $records, array $columns = null, array $parameters = null);

    /**
     * 将值转换为记录
     *
     * @param mixed $value 字段值
     * @param array $columns|null 字段值表列
     * @param array $parameters|null 字段参数
     * @return array|null
     */
    public function toRecords($value, array $columns = null, array $parameters = null): ?array;
}
