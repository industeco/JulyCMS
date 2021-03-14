<?php

namespace App\EntityField\FieldTypes;

use App\EntityField\FieldBase;
use App\Services\Validation\RuleFormats\JsRule;
use App\Services\Validation\RuleGroup;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 模型字段类型类
 */
abstract class FieldTypeBase
{
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
    protected $description = null;

    /**
     * 视图
     *
     * @var string
     */
    protected $view;

    /**
     * 字段值类型转换器
     *
     * @var string|\Closure
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
    protected $valueModel = \App\EntityValue\FieldValue::class;

    /**
     * 绑定的字段对象
     *
     * @var \App\EntityField\FieldBase|null
     */
    protected $field = null;

    /**
     * 字段构造元数据
     *
     * @var array
     */
    protected $meta = [];

    /**
     * 字段元数据结构
     *
     * @var array
     */
    protected $metaSchema = [
        'label' => [
            'type' => 'string',
            'default' => null,
        ],
        'description' => [
            'type' => 'string',
            'default' => null,
        ],
        'maxlength' => [
            'type' => 'int',
            'default' => null,
        ],
        'required' => [
            'type' => 'bool',
            'default' => false,
        ],
        'rules' => [
            'type' => 'string',
            'default' => null,
        ],
        'options' => [
            'type' => 'string',
            'default' => null,
        ],
        'value' => [
            'type' => 'string',
            'default' => null,
        ],
        'helptext' => [
            'type' => 'string',
            'default' => null,
        ],
        'placeholder' => [
            'type' => 'string',
            'default' => null,
        ],
    ];

    /**
     * @param \App\EntityField\FieldBase|null $field
     */
    public function __construct(FieldBase $field = null)
    {
        $this->field = $field;
        if ($field) {
            $this->meta = $field->getMeta();
        }

        $classBaseName = preg_replace('/Type$/', '', class_basename(static::class));

        // 生成标签
        if (! $this->label) {
            $this->label = $classBaseName;
        }

        // 指定视图
        if (! $this->view) {
            $this->view = 'field_type.'.Str::kebab($classBaseName);
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
        $this->meta = $field->getMeta();

        return $this;
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
        return $this->view;
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
     * @return \App\EntityValue\FieldValueBase
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
     * 获取字段值模型，用于管理字段值的增删改查等
     *
     * @return array
     */
    public function getMeta()
    {
        if (!$this->meta && $this->field) {
            return $this->meta = $this->field->getMeta();
        }
        return $this->meta ?? [];
    }

    /**
     * 获取字段值模型，用于管理字段值的增删改查等
     *
     * @return array
     */
    public function getMetaSchema()
    {
        $schema = is_array($this->metaSchema) ? $this->metaSchema : [];
        $schema['value'] = [
            'type' => $this->getCaster(),
            'default' => $schema['value']['default'] ?? null,
        ];

        return $schema;
    }

    /**
     * 从表单数据中提取字段参数，主要用于类型翻译
     *
     * @param array $formData 包含表单数据的数组
     * @return array
     */
    public function extractMeta(array $formData)
    {
        $meta = [];
        foreach ($this->getMetaSchema() as $key => $schema) {
            if (isset($formData[$key])) {
                $meta[$key] = Types::cast($formData[$key], $schema['type']);
            } elseif ($schema['default'] ?? null) {
                $meta[$key] = $schema['default'];
            }
        }

        return $meta;
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
        $meta = $this->meta;
        $meta['value'] = $value;
        $meta['rules'] = array_values($this->getRules($meta)->parseTo(new JsRule));

        return view($this->getView(), $meta)->render();
    }

    /**
     * 获取验证规则（用于前端 js 验证）
     *
     * @param  array|null $meta 字段元数据
     * @return \App\Services\Validation\RuleGroup
     */
    public function getRules(array $meta = null)
    {
        $meta = $meta ?? $this->field->getMeta();

        $rules = RuleGroup::make($meta['rules'] ?? '', $this->field->getKey());

        // 补充 required 规则
        if (($meta['required'] ?? false) && !$rules->hasRule('required')) {
            $rules->addRules('required');
        }

        // 补充 max 规则
        if (($meta['maxlength'] ?? null) && !$rules->hasRule('max')) {
            $rules->addRules('max:'.$meta['maxlength']);
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

    /**
     * 转为适合索引的内容
     *
     * @param  string $value 字段内容
     * @return string
     */
    public function toIndex($value)
    {
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value, ' ;');
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
}
