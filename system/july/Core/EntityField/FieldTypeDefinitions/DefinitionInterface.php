<?php

namespace July\Core\EntityField\FieldTypeDefinitions;

use July\Core\EntityField\EntityFieldInterface;

interface DefinitionInterface
{
    /**
     * 获取指定属性
     *
     * @param  string $attribute
     * @param  mixed $default
     * @return mixed
     */
    public static function get($attribute, $default = null);

    /**
     * 设置字段对象
     *
     * @param  array $field
     * @return self
     */
    public function setField(EntityFieldInterface $field);

    /**
     * 设置字段参数读取语言
     *
     * @param  string $langcode
     * @return self
     */
    public function setLangcode($langcode);

    /**
     * 获取该类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @return array
     */
    public function getParametersSchema(): array;

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 包含表单数据的数组
     * @return array
     */
    public function extractParameters(array $raw): array;

    /**
     * 字段数据存储表的列信息，结构：
     * [
     *     [
     *         type => string,
     *         name => string,
     *         parameters => array,
     *     ],
     *     ...
     * ]
     *
     * @param  string|null $fieldName
     * @param  array|null $parameters
     * @return array
     */
    public function getColumns($fieldName = null, array $parameters = null): array;

    /**
     * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
     *
     * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return array
     */
    public function getMaterials(array $data = null): array;

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return string
     */
    public function getComponent(array $data = null): ?string;

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getRules(array $parameters = null): array;

    /**
     * 获取验证器（用于后端验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getValidator(array $parameters = null): array;

    /**
     * 将记录转换为值
     *
     * @param  array $records 表记录
     * @param  array $columns|null 字段值表列
     * @param  array $parameters|null 字段参数
     * @return mixed
     */
    public function toValue(array $records, array $columns = null, array $parameters = null);

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @param  array $columns|null 字段值表列
     * @param  array $parameters|null 字段参数
     * @return array|null
     */
    public function toRecords($value, array $columns = null, array $parameters = null): ?array;
}
