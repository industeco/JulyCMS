<?php

namespace July\Core\EntityField\FieldTypeDefinitions;

use App\Traits\HasAttributesTrait;
use App\Utils\Types;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use July\Core\EntityField\EntityFieldInterface;

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
     * @var \July\Core\EntityField\EntityFieldBase
     */
    protected $field = null;

    /**
     * 字段参数读取语言
     *
     * @var string|null
     */
    protected $langcode = null;

    /**
     * @param \July\Core\EntityField\EntityFieldBase|null $field
     * @param string|null $langcode
     */
    public function __construct(EntityFieldInterface $field = null)
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public static function get($attribute, $default = null)
    {
        return (new static)->getAttribute($attribute) ?? null;
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
     * 获取类型 id
     *
     * @param string|null $id
     * @return string
     */
    public function getIdAttribute($id)
    {
        return $this->getKey();
    }

    /**
     * 获取类型标签
     *
     * @param string|null $label
     * @return string
     */
    public function getLabelAttribute($label)
    {
        return $label ?? class_basename(static::class);
    }

    /**
     * {@inheritdoc}
     */
    public function setField(EntityFieldInterface $field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;

        return $this;
    }

    /**
     * 获取该类型字段参数的模式（属性，属性值类型，默认值等）
     *
     * @return array
     */
    public function getParametersSchema(): array
    {
        $schema = [];
        $defaultSchema = config('jc.entity_field.parameters_meta');
        foreach ($this->getAttribute('schema') ?? [] as $key => $value) {
            if (is_int($key)) {
                $key = $value;
            }
            if (is_string($key)) {
                $schema[$key] = array_merge($defaultSchema[$key] ?? [], is_array($value) ? $value : []);
            }
        }

        $schema['type'] = array_merge($defaultSchema['type'] ?? [], $schema['type'] ?? []);
        $schema['required'] = array_merge($defaultSchema['required'] ?? [], $schema['required'] ?? []);

        return $schema;
    }

    /**
     * {@inheritdoc}
     */
    public function extractParameters(array $raw): array
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
     * {@inheritdoc}
     */
    public function getColumns($fieldName = null, array $parameters = null): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMaterials(array $data = null): array
    {
        $data = $data ?? $this->field->gather($this->langcode);
        return [
            'id' => $data['id'],
            'data' => $data,
            'field_type_id' => $this->id,
            'value' => null,
            'element' => $this->getComponent($data),
            'rules' => $this->getRules($data['parameters'] ?? []),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getComponent(?array $data = null): ?string
    {
        $data = $data ?? $this->field->gather($this->langcode);
        $data['parameters']['helptext'] = $data['parameters']['helptext'] ?? $data['description'] ?? null;
        return view('backend::components.'.$this->id, $data)->render();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getValidator(array $parameters = null): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function toValue(array $records, array $columns = null, array $parameters = null)
    {
        $columns = $columns ?? $this->getColumns();
        $name = $columns[0]['name'];
        return trim($records[0][$name]);
    }

    /**
     * {@inheritdoc}
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
