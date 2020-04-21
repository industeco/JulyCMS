<?php

namespace App\FieldTypes;

class FileField extends FieldTypeBase
{
    public static $title = 'File Name';

    public static $description = '适用于文件名，带文件浏览按钮';

    public static $searchable = false;

    public static function columns(array $config)
    {
        $column = [
            'type' => 'string',
            'parameters' => [
                'length' => 100,
            ],
        ];
        return [$column];
    }

    public static function configStructure(): array
    {
        return [
            'required' => 'boolean',
            'file_type' => 'string',
            'validations' => 'array',
            'interface_values' => [
                'label' => 'string',
                'help' => 'string',
                'description' => 'string',
            ],
        ];
    }

    public static function parameters(array $data)
    {
        $parameters = array_merge([
            'help' => '',
            'length' => 100,
        ], describe($data));

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('rules.file_type.'.$fileType)) {
                $parameters['help'] = '允许的扩展名：'.join(', ', $exts);
            }
        }

        return $parameters;
    }

    public static function rules(array $parameters)
    {
        $rules = parent::rules($parameters);

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('rules.file_type.'.$fileType)) {
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
