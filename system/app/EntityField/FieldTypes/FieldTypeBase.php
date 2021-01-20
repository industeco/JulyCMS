<?php

namespace App\EntityField\FieldTypes;

use App\Traits\HasAttributesTrait;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\EntityField\FieldBase;

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
     * 字段值类型转换器
     *
     * @var string
     */
    protected $caster = 'string';

    /**
     * 绑定的字段对象
     *
     * @var \App\EntityField\FieldBase|null
     */
    protected $field = null;

    // /**
    //  * 字段参数读取语言
    //  *
    //  * @var string|null
    //  */
    // protected $langcode = null;

    // /**
    //  * 指示字段是否已翻译
    //  *
    //  * @var bool
    //  */
    // protected $translated = false;

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

    // /**
    //  * 设置语言版本
    //  *
    //  * @param  string $langcode
    //  * @return static
    //  */
    // public function translateTo(string $langcode)
    // {
    //     $this->langcode = $langcode;
    //     if ($this->field) {
    //         $this->field->translateTo($langcode);
    //     }

    //     return $this;
    // }

    // /**
    //  * 获取字段定义语言
    //  *
    //  * @param  string $langcode
    //  * @return static
    //  */
    // public function getLangcode()
    // {
    //     if ($this->field) {
    //         return $this->field->getLangcode();
    //     }

    //     return $this->langcode;
    // }

    public function getDefaultValue()
    {
        return null;
    }

    /**
     * 从表单数据中提取字段参数
     *
     * @param array $raw 包含表单数据的数组
     * @return array
     */
    public function extractParameters(array $raw)
    {
        $raw = $raw['parameters'] ?? $raw;
        $parameters = [];

        // 默认值
        if (isset($raw['default'])) {
            $parameters['default'] = Types::cast($raw['default'], $this->caster);
        }

        // 可选项
        $options = $raw['options'] ?? null;
        if ($options && is_array($options)) {
            $parameters['options'] = array_map(function($option) {
                return Types::cast($option, $this->caster);
            }, $options);
        }

        // 占位提示
        if (isset($raw['placeholder'])) {
            $parameters['placeholder'] = trim($raw['placeholder']);
        }

        return $parameters;
    }

    /**
     * 获取存储表表名
     *
     * @return string|null
     */
    public function getTable()
    {
        return null;
    }

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
     * @return array
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
     *
     * @return array
     */
    public function getMaterials()
    {
        $data = $this->field->gather();
        return [
            'id' => $data['id'],
            'field_type_id' => $this->id,
            'value' => null,
            'element' => $this->getComponent($data),
            'rules' => $this->getRules(),
        ];
    }

    /**
     * 获取表单组件（element-ui component）
     *
     * @param  array|null $data 字段数据
     * @return string
     */
    public function getComponent(?array $data = [])
    {
        $data = $data ?: $this->field->gather();

        if (! isset($data['parameters']['helptext'])) {
            $data['parameters']['helptext'] = $data['description'] ?? null;
        }

        return view('backend::components.'.$this->id, $data)->render();
    }

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @return array
     */
    public function getRules()
    {
        $rules = [];
        $parameters = $this->field->getParameters();
        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }
        return $rules;
    }

    /**
     * 获取验证器（用于后端验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getValidator(?array $parameters = [])
    {
        return [];
    }

    /**
     * 将记录转换为值
     *
     * @param  array $records 表记录
     * @param  array|null $columns 字段值表列
     * @param  array|null $parameters 字段参数
     * @return mixed
     */
    public function toValue(array $records, ?array $columns = [], ?array $parameters = [])
    {
        $columns = $columns ?: $this->getColumns();
        $name = $columns[0]['name'];

        return trim($records[0][$name]);
    }

    /**
     * 将值转换为记录
     *
     * @param  mixed $value 字段值
     * @param  array|null $columns 字段值表列
     * @param  array|null $parameters 字段参数
     * @return array|null
     */
    public function toRecords($value, ?array $columns = [], ?array $parameters = [])
    {
        if (! strlen($value)) {
            return null;
        }

        $columns = $columns ?: $this->getColumns();

        return [
            [
                $columns[0]['name'] => $value,
            ],
        ];
    }

    public function __get($name)
    {
        return $this->$name ?? null;
    }
}
