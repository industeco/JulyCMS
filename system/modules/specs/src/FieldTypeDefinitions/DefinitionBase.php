<?php

namespace Specs\FieldTypeDefinitions;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class DefinitionBase implements DefinitionInterface
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = null;

    /**
     * 字段类型标题
     *
     * @var string
     */
    protected $label = null;

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = null;

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $parameters = [
        'nullable' => true,
    ];

    /**
     * 字段数据
     *
     * @var array
     */
    protected $field;

    /**
     * @param array $field
     */
    public function __construct(array $field = [])
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = Str::snake(basename(str_replace('\\', '/', static::class), 'Type'));
        }
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        if (!$this->label) {
            $this->label = basename(str_replace('\\', '/', static::class), 'Type');
        }
        return $this->label;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function attributesToArray()
    {
        return [
            'id' => $this->getId(),
            'label' => $this->getLabel(),
            'description' => $this->getDescription(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $parameters = [];
        if ($this->field['is_unique'] ?? false) {
            $parameters['unique'] = true;
        }

        return array_merge($this->parameters, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefault()
    {
        if (is_null($default = $this->field['default'] ?? null)) {
            return null;
        }
        return $this->cast($default);
    }

    /**
     * {@inheritdoc}
     */
    public function bind(array $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [
            [
                'type' => $this->type,
                'name' => $this->field['field_id'],
                'parameters' => $this->getParameters(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function cast($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function escape($value)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function toValue(array $records)
    {
        $record = Arr::first($records);
        $column = $this->getColumns()[0];
        return $this->cast($record[$column['name']] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function toRecords($value)
    {
        $column = $this->getColumns()[0];
        return [$column['name'] => $this->escape($value)];
    }


    // /**
    //  * 获取用于构建「字段生成/编辑表单」的材料，包括 HTML 片段，前端验证规则等
    //  *
    //  * @param  array|null $data 字段数据 = 固定属性(attributes) + 参数(parameters)
    //  * @return array
    //  */
    // public function getMaterials(?array $data = [])
    // {
    //     $data = $data ?: $this->field->gather();

    //     return [
    //         'id' => $data['id'],
    //         'field_type_id' => $this->getKey(),
    //         'value' => null,
    //         'element' => $this->getComponent($data),
    //         'rules' => $this->getRules($data['parameters'] ?? []),
    //     ];
    // }

    // /**
    //  * 获取表单组件（element-ui component）
    //  *
    //  * @param  array|null $data 字段数据
    //  * @return string
    //  */
    // public function getComponent(?array $data = [])
    // {
    //     $data = $data ?: $this->field->gather();

    //     if (! isset($data['parameters']['helptext'])) {
    //         $data['parameters']['helptext'] = $data['description'] ?? null;
    //     }

    //     return view('components.'.$this->getKey(), $data)->render();
    // }

    // /**
    //  * 获取验证规则（用于前端 js 验证）
    //  *
    //  * @param  array|null $parameters 字段参数
    //  * @return array
    //  */
    // public function getRules(?array $parameters = [])
    // {
    //     $parameters = $parameters ?: $this->field->getParameters();

    //     $rules = [];

    //     if ($parameters['required'] ?? false) {
    //         $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
    //     }

    //     $max = $parameters['maxlength'] ?? 0;
    //     if ($max > 0) {
    //         $rules[] = "{max:{$max}, message:'最多 {$max} 个字符', trigger:'change'}";
    //     }

    //     return $rules;
    // }

    // /**
    //  * 获取验证器（用于后端验证）
    //  *
    //  * @param  array|null $parameters 字段参数
    //  * @return array
    //  */
    // public function getValidator(?array $parameters = [])
    // {
    //     return [];
    // }

    // /**
    //  * 将记录转换为值
    //  *
    //  * @param  array $records 表记录
    //  * @param  array|null $columns 字段值表列
    //  * @param  array|null $parameters 字段参数
    //  * @return mixed
    //  */
    // public function toValue(array $records, ?array $columns = [], ?array $parameters = [])
    // {
    //     $columns = $columns ?: $this->getColumns();
    //     $name = $columns[0]['name'];

    //     return trim($records[0][$name]);
    // }

    // /**
    //  * 将值转换为记录
    //  *
    //  * @param  mixed $value 字段值
    //  * @param  array|null $columns 字段值表列
    //  * @param  array|null $parameters 字段参数
    //  * @return array|null
    //  */
    // public function toRecords($value, ?array $columns = [], ?array $parameters = [])
    // {
    //     if (! strlen($value)) {
    //         return null;
    //     }

    //     $columns = $columns ?: $this->getColumns();

    //     return [
    //         [
    //             $columns[0]['name'] => $value,
    //         ],
    //     ];
    // }

    // /**
    //  * 获取类型标签
    //  *
    //  * @param  string|null $label
    //  * @return string
    //  */
    // public function getLabelAttribute(?string $label)
    // {
    //     return $label ?? class_basename(static::class);
    // }

    // /**
    //  * 获取当前类型字段参数的模式（属性，属性值类型，默认值等）
    //  *
    //  * @param  array|null $schema
    //  * @return array
    //  */
    // public function getParametersSchema()
    // {
    //     $default = config('jc.entity_field.parameters_schema');
    //     $result = [
    //         'type' => $default['type'] ?? [],
    //         'required' => $default['required'] ?? [],
    //     ];

    //     foreach ($this->attributes['schema'] ?? [] as $key => $value) {
    //         if (is_int($key)) {
    //             $key = $value;
    //             $value = [];
    //         }
    //         if (is_string($key)) {
    //             $result[$key] = array_merge($default[$key] ?? [], is_array($value) ? $value : []);
    //         }
    //     }

    //     return $result;
    // }
}
