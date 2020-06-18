<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class TextField extends FieldTypeBase
{
    /**
     * {@inheritDoc}
     */
    public static function getAlias(): string
    {
        return 'text';
    }

    /**
     * {@inheritDoc}
     */
    public static function getLabel(): string
    {
        return '文字';
    }

    /**
     * {@inheritDoc}
     */
    public static function getDescription(): ?string
    {
        return '适用于无格式内容';
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema(): array
    {
        $schema = parent::getSchema();

        return array_merge($schema, [
            'maxlength' => [
                'value_type' => 'integer',
                'default' => 200,
            ],
            'pattern' => [
                'value_type' => 'string',
            ],
            'placeholder' => [
                'value_type' => 'string',
            ],
            'default' => [
                'value_type' => 'string',
            ],
            'datalist' => [
                'value_type' => 'array',
                'default' => [],
            ],
            'helptext' => [
                'value_type' => 'string',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns($fieldName = null, array $parameters = null): array
    {
        $parameters = $parameters ?? $this->field->parameters($this->langcode);
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
     * {@inheritDoc}
     */
    public function getRules(array $parameters = null): array
    {
        $parameters = $parameters ?? $this->field->parameters($this->langcode);

        $rules = parent::getRules($parameters);

        if ($pattern = $parameters['pattern'] ?? null) {
            if ($pattern = config('jc.rules.pattern.'.$pattern)) {
                $rules[] = "{pattern:$pattern, message:'格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }
}
