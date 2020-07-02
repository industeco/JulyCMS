<?php

namespace App\Entity\FieldTypes;

use App\Entity\Models\BaseEntityField;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class BaseFieldType implements FieldTypeInterface
{
    /**
     * 字段对象
     *
     * @var \App\Models\BaseEntityField
     */
    protected $field = null;

    /**
     * 字段参数读取语言
     *
     * @var string|null
     */
    protected $langcode = null;

    /**
     * {@inheritDoc}
     */
    public static function alias(): string
    {
        return strtolower(preg_replace('/Field$/', '', class_basename(static::class)));
    }

    /**
     * {@inheritDoc}
     */
    public static function description(): ?string
    {
        return null;
    }

    /**
     * @param \App\Models\BaseEntityField|null $field
     * @param string $langcode
     */
    public function __construct(BaseEntityField $field = null, string $langcode = null)
    {
        $this->field = $field;
        $this->langcode = $langcode;
    }

    /**
     * {@inheritDoc}
     */
    public function setField(BaseEntityField $field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema(): array
    {
        return [
            'value_type' => [
                'value_type' => 'string',
                'default' => 'string',
            ],
            'required' => [
                'value_type' => 'boolean',
                'default' => false,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function extractParameters(array $raw): array
    {
        $raw = $raw['parameters'] ?? $raw;
        $parameters = [];
        foreach ($this->getSchema() as $key => $meta) {
            if (isset($raw[$key])) {
                $parameters[$key] = cast_value($raw[$key], $meta['value_type']);
            } elseif (isset($meta['default'])) {
                $parameters[$key] = $meta['default'];
            }
        }
        return $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns($fieldName = null, array $parameters = null): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getJigsaws(array $data = null): array
    {
        $data = $data ?? $this->field->gather($this->langcode);
        return [
            'truename' => $data['truename'] ?? $this->field->getKey(),
            'field_type' => $data['field_type'] ?? static::alias(),
            'value' => null,
            'element' => $this->getElement($data),
            'rules' => $this->getRules($data['parameters'] ?? []),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getElement(array $data = null): ?string
    {
        $data = $data ?? $this->field->gather($this->langcode);
        $data['parameters']['helptext'] = $data['parameters']['helptext'] ?? $data['description'] ?? null;
        return view('admin::components.'.static::alias(), $data)->render();
    }

    /**
     * {@inheritDoc}
     */
    public function getRules(array $parameters = null): array
    {
        $parameters = $parameters ?? $this->field->getParameters($this->langcode);

        $rules = [];

        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }

        $max = $parameters['maxlength'] ?? 0;
        if ($max > 0) {
            $rules[] = "{max:{$max}, message: '最多 {$max} 个字符', trigger: 'change'}";
        }

        return $rules;
    }

    /**
     * {@inheritDoc}
     */
    public function getValidator(array $parameters = null): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function toValue(array $records, array $columns = null, array $parameters = null)
    {
        $columns = $columns ?? $this->getColumns();
        $name = $columns[0]['name'];
        return trim($records[0][$name]);
    }

    /**
     * {@inheritDoc}
     */
    public function toRecords($value, array $columns = null, array $parameters = null): ?array
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