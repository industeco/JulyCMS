<?php

namespace App\FieldTypes;

class FileField extends FieldTypeBase
{
    /**
     * {@inheritdoc}
     */
    public static function getAlias(): string
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabel(): string
    {
        return '文件名';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDescription(): string
    {
        return '带文件浏览按钮';
    }

    public function getColumns($fieldName, array $parameters = []): array
    {
        $column = [
            'type' => 'string',
            'name' => $fieldName.'_value',
            'parameters' => [
                'length' => 200,
            ],
        ];
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
                'default' => 200,
            ],
            'file_type' => [
                'type' => 'string',
            ],
            'helptext' => [
                'type' => 'string',
            ],
        ];
    }

    public function collectParameters(array $raw): array
    {
        $parameters = parent::collectParameters($raw);
        if ($parameters['helptext'] ?? null) {
            return $parameters;
        }

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('jc.rules.file_type.'.$fileType)) {
                $parameters['helptext'] = '允许的扩展名：'.join(', ', $exts);
            }
        }

        return $parameters;
    }

    public function getRules(array $parameters)
    {
        $rules = parent::getRules($parameters);

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('jc.rules.file_type.'.$fileType)) {
                $exts = join('|', $exts);
                $rules[] = "{pattern: /^(\\/[a-z0-9\\-_]+)+\\.($exts)$/, message:'文件格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }

    public function getElement(array $fieldData)
    {
        return view('admin::components.file', $fieldData)->render();
    }
}
