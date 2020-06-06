<?php

namespace App\FieldTypes;

class FileField extends FieldTypeBase
{
    public static $label = '文件名';

    public static $description = '带文件浏览按钮';

    public static $searchable = false;

    public static function columns(array $config)
    {
        $column = [
            'type' => 'string',
            'parameters' => [
                'length' => 200,
            ],
        ];
        return [$column];
    }

    public static function configStructure(): array
    {
        return [
            'required' => [
                'cast' => 'boolean',
            ],
            'file_type' => [
                'cast' => 'string',
            ],
            'length' => [
                'cast' => 'integer',
                'default' => 200,
            ],
            'validations' => [
                'cast' => 'array',
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
        ];
    }

    public static function parameters(array $data)
    {
        $data = parent::parameters($data);

        if ($fileType = $data['file_type'] ?? null) {
            if ($exts = config('jc.rules.file_type.'.$fileType)) {
                $data['help'] = '允许的扩展名：'.join(', ', $exts);
            }
        }

        return $data;
    }

    public static function rules(array $parameters)
    {
        $rules = parent::rules($parameters);

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('jc.rules.file_type.'.$fileType)) {
                $exts = join('|', $exts);
                $rules[] = "{pattern: /^(\\/[a-z0-9\\-_]+)+\\.($exts)$/, message:'文件名格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }

    public static function element(array $parameters)
    {
        return view('admin::components.file', $parameters)->render();
    }
}
