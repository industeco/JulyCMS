<?php

namespace App\EntityField\FieldTypeDefinitions;

use Illuminate\Support\Facades\Log;

class Text extends DefinitionBase
{
    protected $attributes = [
        'id' => 'text',
        'label' => '文字',
        'description' => '适用于无格式内容',
        'schema' => [
            'maxlength' => [
                'default' => 200,
            ],
            'pattern',
            'placeholder',
            'default',
            'options',
            'helptext',
        ],
    ];

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
