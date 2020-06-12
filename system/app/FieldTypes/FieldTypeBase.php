<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\Log;
use App\Models\NodeField;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class FieldTypeBase implements FieldTypeInterface
{
    /**
     * 字段对象
     *
     * @var \App\Models\NodeField
     */
    protected $field = null;

    /**
     * 字段名
     *
     * @var string|null
     */
    protected $fieldName = null;

    /**
     * 字段本征属性
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * 字段参数
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * {@inheritdoc}
     */
    public static function getDescription(): string
    {
        return '';
    }

    public function __construct(NodeField $field = null, $langcode = null)
    {
        if ($field) {
            $this->field = $field;
            $this->fieldName = $field->getKey();
            $this->attributes = $field->attributesToArray();
            $this->parameters = $field->parameters($langcode);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema(): array
    {
        return [
            'type' => [
                'type' => 'string',
                'default' => 'string',
            ],
            'required' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function collectParameters(array $raw): array
    {
        $parameters = [];
        foreach ($this->getSchema() as $key => $meta) {
            $value = $raw[$key] ?? $raw['parameters__'.$key] ?? null;
            if (!is_null($value)) {
                $parameters[$key] = cast_value($value, $meta['type'] ?? 'string');
            }
        }
        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns(array $parameters = null, $fieldName = null): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getJigsaws(array $parameters = null, array $attributes = null): array
    {
        $parameters = $parameters ?: $this->parameters;
        $attributes = $attributes ?: $this->attributes;
        return [
            'truename' => $attributes['truename'] ?? $this->fieldName,
            'field_type' => $attributes['field_type'] ?? $this->getAlias(),
            'value' => null,
            'element' => $this->getElement($parameters, $attributes),
            'rules' => $this->getRules($parameters),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getElement(?array $parameters = null, ?array $attributes = null): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(array $parameters = null): array
    {
        $parameters = $parameters ?: $this->parameters;

        $rules = [];

        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }

        $max = $parameters['max'] ?? 0;
        if ($max > 0) {
            $rules[] = "{max:{$max}, message: '最多 {$max} 个字符', trigger: 'change'}";
        }

        return $rules;
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(?array $parameters = null): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toValue(array $records, array $columns = null, array $parameters = null)
    {
        $columns = $columns ?? $this->getColumns();
        $columnName = $columns[0]['name'];
        return trim($records[0][$columnName]);
    }

    /**
     * {@inheritdoc}
     */
    public function toRecords($value, array $columns = null, array $parameters = null): array
    {
        $columns = $columns ?? $this->getColumns();

        if (!strlen($value)) {
            return null;
        }

        return [
            [
                $columns[0]['name'] => $value,
            ],
        ];
    }
}
