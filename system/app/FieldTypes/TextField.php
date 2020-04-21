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
            'length' => 'integer',
            'required' => 'boolean',
            'index_weight' => 'integer',
            'interface_values' => [
                'label' => 'string',
                'help' => 'string',
                'description' => 'string',
            ],
            'content_values' => [
                'placeholder' => 'string',
                'default' => 'string',
                'datalist' => 'array',
            ],
        ];
    }

    public static function parameters(array $data)
    {
        return array_merge([
            'length' => 255,
            'help' => '',
        ], describe($data));
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
