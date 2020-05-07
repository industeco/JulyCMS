<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\View;

class TextField extends FieldTypeBase
{
    public static $title = 'Text';

    public static $description = '适用于无格式内容，如页面描述，图片 alt 等';

    public static function columns(array $config)
    {
        $column = [
            'type' => 'string',
            'parameters' => $config['parameters'] ?? [],
        ];
        return [$column];
    }

    public static function configStructure(): array
    {
        return [
            'length' => [
                'cast' => 'integer',
                'default' => 255,
            ],
            'required' => [
                'cast' => 'boolean',
            ],
            'index_weight' => [
                'cast' => 'integer',
            ],
            'label' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
            'help' => [
                'type' => 'interface_value',
                'cast' => 'string',
                'default' => '',
            ],
            'description' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
            'placeholder' => [
                'type' => 'content_value',
                'cast' => 'string',
            ],
            'default' => [
                'type' => 'content_value',
                'cast' => 'string',
            ],
            'datalist' => [
                'type' => 'content_value',
                'cast' => 'array',
            ],
        ];
    }

    public static function rules(array $parameters)
    {
        $rules = parent::rules($parameters);

        if ($pattern = $parameters['pattern'] ?? null) {
            if ($pattern = config('rules.pattern.'.$pattern)) {
                $rules[] = "{pattern: $pattern, message:'格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }

    public static function element(array $parameters)
    {
        return view('admin::components.text', $parameters)->render();
    }
}
