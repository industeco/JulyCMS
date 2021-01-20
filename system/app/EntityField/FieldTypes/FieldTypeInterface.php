<?php

namespace App\EntityField\FieldTypes;

use App\EntityField\FieldBase;

interface FieldTypeInterface
{
    /**
     * 获取指定属性
     *
     * @param  string $attribute
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $attribute, $default = null);

    /**
     * 设置字段对象
     *
     * @param  array $field
     * @return self
     */
    public function bindField(FieldBase $field);

    /**
     * 设置字段参数读取语言
     *
     * @param  string $langcode
     * @return self
     */
    public function translateTo(string $langcode);

    /**
     * 获取当前类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @return array
     */
    public function getParametersSchema();

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 包含表单数据的数组
     * @return array
     */
    public function extractParameters(array $raw);

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
     * @param  string $fieldName
     * @param  array|null $parameters
     * @return array
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = []);

    /**
     * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
     *
     * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return array
     */
    public function getMaterials(?array $data = []);

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  array|null $data 字段数据
     * @return string
     */
    public function getComponent(?array $data = []);

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getRules(?array $parameters = []);

    /**
     * 获取验证器（用于后端验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getValidator(?array $parameters = []);

    /**
     * 将记录转换为值
     *
     * @param  array $records 表记录
     * @param  array|null $columns 字段值表列
     * @param  array|null $parameters 字段参数
     * @return mixed
     */
    public function toValue(array $records, ?array $columns = [], ?array $parameters = []);

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @param  array|null $columns 字段值表列
     * @param  array|null $parameters 字段参数
     * @return array|null
     */
    public function toRecords($value, ?array $columns = [], ?array $parameters = []);
}
