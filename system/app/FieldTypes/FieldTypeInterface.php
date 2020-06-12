<?php

namespace App\FieldTypes;

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
    public static function getDescription(): string;

    /**
     * 获取该类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @return array
     */
    public function getSchema(): array;

    /**
     * 从表单数据中采集字段参数
     *
     * @param array $raw 表单数据
     * @return array
     */
    public function collectParameters(array $raw): array;

    /**
     * 设置字段参数
     *
     * @param array $parameters
     * @return static
     */
    public function setParameters(array $parameters);

    /**
     * 字段数据存储表的列信息，结构：
     * [[
     *     type => string,
     *     name => string,
     *     parameters => array,
     *   ], ...]
     *
     * @param string|null $fieldName
     * @param array|null $parameters
     * @return array
     */
    public function getColumns(array $parameters = null, $fieldName = null): array;

    /**
     * 生成表单拼图，包括 HTML 片段和前端验证规则
     *
     * @param array|null $parameters 字段参数
     * @param array|null $attributes 字段属性
     * @return array
     */
    public function getJigsaws(array $parameters = null, array $attributes = null): array;

    /**
     * 获取字段的 HTML 片段（element-ui 组件）
     *
     * @param array|null $parameters 字段参数
     * @param array|null $attributes 字段属性
     * @return string
     */
    public function getElement(array $parameters = null, array $attributes = null): string;

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
     * @param array|null $columns 字段值表列
     * @param array|null $parameters 字段参数
     * @return mixed
     */
    public function toValue(array $records, array $columns = null, array $parameters = null);

    /**
     * 将值转换为记录
     *
     * @param mixed $value 字段值
     * @param array|null $columns 字段值表列
     * @param array|null $parameters 字段参数
     * @return array
     */
    public function toRecords($value, array $columns = null, array $parameters = null): array;
}
