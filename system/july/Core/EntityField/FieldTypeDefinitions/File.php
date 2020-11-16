<?php

namespace July\Core\EntityField\FieldTypeDefinitions;

class File extends DefinitionBase
{
    protected $attributes = [
        'id' => 'file',
        'label' => '文件名',
        'description' => '可输入带路径文件名，输入框右侧带文件浏览按钮',
        'schema' => [
            'maxlength' => [
                'default' => 200,
            ],
            'file_bundle',
            'helptext',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function extractParameters(array $raw): array
    {
        $parameters = parent::extractParameters($raw);

        if ($parameters['helptext'] ?? null) {
            return $parameters;
        }

        if ($fileBundle = $parameters['file_bundle'] ?? null) {
            if ($exts = config('jc.validation.file_bundles.'.$fileBundle)) {
                $parameters['helptext'] = '允许的扩展名：'.join(', ', $exts);
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = []): array
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
     * {@inheritdoc}
     */
    public function getRules(?array $parameters = []): array
    {
        $parameters = $parameters ?: $this->field->getParameters();

        $rules = parent::getRules($parameters);

        if ($fileBundle = $parameters['file_bundle'] ?? null) {
            if ($exts = config('jc.validation.file_bundles.'.$fileBundle)) {
                $exts = join('|', $exts);
                $rules[] = "{pattern: /^(\\/[a-z0-9\\-_]+)+\\.($exts)$/, message:'文件格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }
}
