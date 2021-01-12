<?php

namespace App\EntityField\FieldTypeDefinitions;

use App\Traits\HasAttributesTrait;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\EntityField\EntityFieldBase;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class DefinitionBase implements DefinitionInterface
{
    use HasAttributesTrait;

    /**
     * 字段对象
     *
     * @var \App\EntityField\EntityFieldBase
     */
    protected $field = null;

    /**
     * 字段参数读取语言
     *
     * @var string|null
     */
    protected $langcode = null;

    /**
     * 指示字段是否已翻译
     *
     * @var bool
     */
    protected $translated = false;

    /**
     * @param \App\EntityField\EntityFieldBase|null $field
     */
    public function __construct(EntityFieldBase $field = null)
    {
        $this->field = $field;
    }

    /**
     * 获取类型 id
     *
     * @return string
     */
    public function getKey()
    {
        return $this->attributes['id'] ?? Str::snake(class_basename(static::class));
    }

    /**
     * 获取指定属性
     *
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return (new static)->getAttribute($key) ?? null;
    }

    /**
     * 绑定字段对象
     *
     * @param  array $field
     * @return self
     */
    public function bindField(EntityFieldBase $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * 设置语言版本
     *
     * @param  string $langcode
     * @return static
     */
    public function translateTo(string $langcode)
    {
        $this->langcode = $langcode;
        if ($this->field) {
            $this->field->translateTo($langcode);
        }

        return $this;
    }

    /**
     * 获取字段定义语言
     *
     * @param  string $langcode
     * @return static
     */
    public function getLangcode()
    {
        if ($this->field) {
            return $this->field->getLangcode();
        }

        return $this->langcode;
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
        foreach ($this->getParametersSchema() as $key => $meta) {
            if (isset($raw[$key])) {
                $parameters[$key] = Types::cast($raw[$key], $meta['cast'] ?? null);
            } elseif (isset($meta['default'])) {
                $parameters[$key] = $meta['default'];
            }
        }

        return $parameters;
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
     * @param  string|null $fieldName
     * @param  array|null $parameters
     * @return array
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = [])
    {
        return [];
    }

    /**
     * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
     *
     * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
     * @return array
     */
    public function getMaterials(?array $data = [])
    {
        $data = $data ?: $this->field->gather();

        return [
            'id' => $data['id'],
            'field_type_id' => $this->getKey(),
            'value' => null,
            'element' => $this->getComponent($data),
            'rules' => $this->getRules($data['parameters'] ?? []),
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

        return view('backend::components.'.$this->getKey(), $data)->render();
    }

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  array|null $parameters 字段参数
     * @return array
     */
    public function getRules(?array $parameters = [])
    {
        $parameters = $parameters ?: $this->field->getParameters();

        $rules = [];

        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }

        $max = $parameters['maxlength'] ?? 0;
        if ($max > 0) {
            $rules[] = "{max:{$max}, message:'最多 {$max} 个字符', trigger:'change'}";
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

    /**
     * 获取类型标签
     *
     * @param  string|null $label
     * @return string
     */
    public function getLabelAttribute(?string $label)
    {
        return $label ?? class_basename(static::class);
    }

    /**
     * 获取当前类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @param  array|null $schema
     * @return array
     */
    public function getParametersSchema()
    {
        $default = config('jc.entity_field.parameters_schema');
        $result = [
            'type' => $default['type'] ?? [],
            'required' => $default['required'] ?? [],
        ];

        foreach ($this->attributes['schema'] ?? [] as $key => $value) {
            if (is_int($key)) {
                $key = $value;
                $value = [];
            }
            if (is_string($key)) {
                $result[$key] = array_merge($default[$key] ?? [], is_array($value) ? $value : []);
            }
        }

        return $result;
    }
}
