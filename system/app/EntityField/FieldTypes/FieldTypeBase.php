<?php

namespace App\EntityField\FieldTypes;

use App\EntityField\FieldBase;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id;

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label;

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description;

    /**
     * 视图
     *
     * @var string|null
     */
    protected $view;

    /**
     * 字段值类型转换器
     *
     * @var string
     */
    protected $caster = 'string';

    /**
     * 字段默认值
     *
     * @var mixed
     */
    protected $defaultValue = null;

    /**
     * 字段值模型类
     *
     * @var string
     */
    protected $valueModel = \App\EntityField\FieldValue::class;

    /**
     * 绑定的字段对象
     *
     * @var \App\EntityField\FieldBase|null
     */
    protected $field = null;

    /**
     * @param \App\EntityField\FieldBase|null $field
     */
    public function __construct(FieldBase $field = null)
    {
        $this->field = $field;

        // 生成 id
        if (! $this->id) {
            $this->id = preg_replace('/_type$/', '', Str::snake(class_basename(static::class)));
        }

        // 生成标签
        if (! $this->label) {
            $this->label = preg_replace('/Type$/', '', class_basename(static::class));
        }
    }

    /**
     * 静态方式获取属性
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return (new static)->$key ?? $default;
    }

    /**
     * 绑定字段对象
     *
     * @param  \App\EntityField\FieldBase $field
     * @return self
     */
    public function bindField(FieldBase $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * 获取类型 id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 获取类型标签
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * 获取类型描述
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * 获取视图
     *
     * @return string|null
     */
    public function getView()
    {
        return $this->view ?? $this->view = 'field_type.'.$this->id;
    }

    /**
     * 获取类型转换器
     *
     * @return string
     */
    public function getCaster()
    {
        return $this->caster;
    }

    /**
     * 获取默认值
     *
     * @return mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * 获取字段值模型，用于管理字段值的增删改查等
     *
     * @return \App\EntityField\FieldValueBase
     */
    public function getValueModel()
    {
        $model = new $this->valueModel;
        if ($this->field) {
            $model->bindField($this->field);
        }
        return $model;
    }

    /**
     * 从表单数据中提取字段参数，主要用于类型翻译
     *
     * @param array $raw 包含表单数据的数组
     * @return array
     */
    public function extractParameters(array $raw)
    {
        return [
            // 默认值
            'default_value' => $raw['default_value'] ?? null,

            // 可选项
            'options' => $raw['options'] ?? '',
        ];
    }

    /**
     * 字段数据存储表的列信息，结构：
     * [
     *     type => string,
     *     name => string,
     *     parameters => array,
     * ]
     *
     * @return array
     */
    public function getColumn()
    {
        return [
            'type' => 'string',
            'name' => $this->field->getKey(),
            'parameters' => [],
        ];
    }

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  mixed $value 字段值
     * @return string
     */
    public function render($value = null)
    {
        $data = $this->field->gather();
        $data['value'] = $value;
        $data['helpertext'] = $data['helpertext'] ?: $data['description'];
        $data['rules'] = $this->getRules($value);

        return view($this->getView(), $data)->render();
    }

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  mixed $value 字段值
     * @return array
     */
    public function getRules($value = null)
    {
        $rules = [];
        if ($this->field->is_required) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }
        return $rules;
    }

    /**
     * 将记录转换为值
     *
     * @param  array $record 表记录
     * @return mixed
     */
    public function toValue(array $record)
    {
        $value = $record[$this->getColumn()['name']] ?? null;
        return Types::cast($value, $this->caster);
    }

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @return array|null
     */
    public function toRecord($value)
    {
        $columns = $this->getColumn();
        return [
            $columns[0]['name'] => $value,
        ];
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }

    // /**
    //  * 获取验证器（用于后端验证）
    //  *
    //  * @param  array|null $parameters 字段参数
    //  * @return array
    //  */
    // public function getValidator()
    // {
    //     return [];
    // }

    // /**
    //  * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
    //  *
    //  * @return array
    //  */
    // public function getMaterials()
    // {
    //     return [
    //         'id' => $this->field->getKey(),
    //         'field_type_id' => $this->id,
    //         'value' => $this->field->getValue(),
    //         'element' => $this->render(),
    //     ];
    // }
}
