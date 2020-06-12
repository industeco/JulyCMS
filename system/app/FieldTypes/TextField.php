<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class TextField extends FieldTypeBase
{
    /**
     * {@inheritdoc}
     */
    public static function getAlias(): string
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabel(): string
    {
        return '文字';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDescription(): string
    {
        return '适用于无格式内容';
    }

    public function getColumns($fieldName, array $parameters = []): array
    {
        $length = $parameters['max'] ?? 0;
        if ($length > 0 && $length <= 255) {
            $column = [
                'type' => 'string',
                'name' => $fieldName.'_value',
                'parameters' => ['length' => $length],
            ];
        } else {
            $column = [
                'type' => 'text',
                'name' => $fieldName.'_value',
                'parameters' => [],
            ];
        }

        return [$column];
    }

    public function getSchema(): array
    {
        return [
            'required' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'max' => [
                'type' => 'integer',
                'default' => 255,
            ],
            'pattern' => [
                'type' => 'string',
            ],
            'placeholder' => [
                'type' => 'string',
            ],
            'default' => [
                'type' => 'string',
            ],
            'datalist' => [
                'type' => 'array',
                'default' => [],
            ],
            'helptext' => [
                'type' => 'string',
            ],
        ];
    }

    public function getRules(array $parameters)
    {
        $rules = parent::getRules($parameters);

        if ($pattern = $parameters['pattern'] ?? null) {
            if ($pattern = config('jc.rules.pattern.'.$pattern)) {
                $rules[] = "{pattern:$pattern, message:'格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }

    public function getElement(array $fieldData)
    {
        return view('admin::components.text', $fieldData)->render();
    }
}
