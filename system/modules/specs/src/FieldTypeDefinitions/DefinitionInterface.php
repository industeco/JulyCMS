<?php

namespace Specs\FieldTypeDefinitions;

interface DefinitionInterface
{
    /**
     * 获取字段类型 id
     *
     * @return string
     */
    public function getId();

    /**
     * 获取字段类型标题
     *
     * @return string
     */
    public function getLabel();

    /**
     * 获取字段类型描述
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * 获取 id, label, description 三属性
     *
     * @return array
     */
    public function attributesToArray();

    /**
     * 获取列参数
     *
     * @return array
     */
    public function getParameters();

    /**
     * 获取默认值
     *
     * @return mixed
     */
    public function getDefault();

    /**
     * 绑定字段数据
     *
     * @param  array $field
     * @return $this
     */
    public function bind(array $field);

    /**
     * 获取数据库列信息，结构：
     * [
     *     [
     *         type => string,
     *         name => string,
     *         parameters => array,
     *     ],
     *     ...
     * ]
     *
     * @return array[]
     */
    public function getColumns();

    /**
     * 转换从数据库取出的值
     *
     * @param  mixed $value
     * @return mixed
     */
    public function cast($value);

    /**
     * 转换将存入数据库的值
     *
     * @param  mixed $value
     * @return mixed
     */
    public function escape($value);

    /**
     * 将记录转换为值
     *
     * @param  array $records 表记录
     * @return mixed
     */
    public function toValue(array $records);

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @return array
     */
    public function toRecords($value);

    // /**
    //  * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
    //  *
    //  * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
    //  * @return array
    //  */
    // public function getMaterials(?array $data = []);

    // /**
    //  * 获取表单组件（element-ui component）
    //  *
    //  * @param  array|null $data 字段数据
    //  * @return string
    //  */
    // public function getComponent(?array $data = []);

    // /**
    //  * 获取验证规则（用于前端 js 验证）
    //  *
    //  * @param  array|null $parameters 字段参数
    //  * @return array
    //  */
    // public function getRules(?array $parameters = []);

    // /**
    //  * 获取验证器（用于后端验证）
    //  *
    //  * @param  array|null $parameters 字段参数
    //  * @return array
    //  */
    // public function getValidator(?array $parameters = []);
}
