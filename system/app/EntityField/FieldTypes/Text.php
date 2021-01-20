<?php

namespace App\EntityField\FieldTypes;

use Illuminate\Support\Facades\Log;

class Text extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'text';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '文字';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '适用于无格式内容';

    /**
     * {@inheritdoc}
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = [])
    {
        $parameters = $parameters ?: $this->field->getParameters();
        $length = $parameters['maxlength'] ?? 0;
        if ($length > 0 && $length <= 255) {
            $column = [
                'type' => 'string',
                'parameters' => ['length' => $length],
            ];
        } else {
            $column = [
                'type' => 'text',
                'parameters' => [],
            ];
        }
        $column['name'] = ($fieldName ?: $this->field->getKey()).'_value';

        return [$column];
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(?array $parameters = [])
    {
        $parameters = $parameters ?: $this->field->getParameters();

        $rules = parent::getRules($parameters);

        if ($pattern = $parameters['pattern'] ?? null) {
            if ($pattern = config('jc.validation.patterns.'.$pattern)) {
                $rules[] = "{pattern:$pattern, message:'格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }
}
