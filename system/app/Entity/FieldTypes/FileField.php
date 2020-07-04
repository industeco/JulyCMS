<?php

namespace App\Entity\FieldTypes;

class FileField extends BaseFieldType
{
    /**
     * {@inheritDoc}
     */
    public static function label(): string
    {
        return '文件名';
    }

    /**
     * {@inheritDoc}
     */
    public static function description(): string
    {
        return '带文件浏览按钮';
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
            'file_type' => [
                'value_type' => 'string',
            ],
            'helptext' => [
                'value_type' => 'string',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function extractParameters(array $raw): array
    {
        $parameters = parent::extractParameters($raw);

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

    /**
     * {@inheritDoc}
     */
    public function getColumns($fieldName = null, ?array $parameters = null): array
    {
        $fieldName = $fieldName ?? $this->field->getKey();
        $column = [
            'type' => 'string',
            'name' => $fieldName.'_value',
            'parameters' => [
                'length' => 200,
            ],
        ];

        return [$column];
    }

    /**
     * {@inheritDoc}
     */
    public function getRules(?array $parameters = null): array
    {
        $parameters = $parameters ?? $this->field->getParameters($this->langcode);

        $rules = parent::getRules($parameters);

        if ($fileType = $parameters['file_type'] ?? null) {
            if ($exts = config('jc.rules.file_type.'.$fileType)) {
                $exts = join('|', $exts);
                $rules[] = "{pattern: /^(\\/[a-z0-9\\-_]+)+\\.($exts)$/, message:'文件格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }
}
