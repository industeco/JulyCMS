<?php

namespace July\Core\EntityField\FieldTypeDefinitions;

class Html extends DefinitionBase
{
    protected $attributes = [
        'id' => 'html',
        'label' => 'HTML',
        'description' => '适用于 HTML 文档',
        'schema' => [
            'helptext',
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = []): array
    {
        $fieldName = $fieldName ?: $this->field->getKey();
        $column = [
            'type' => 'text',
            'name' => $fieldName.'_value',
            'parameters' => [],
        ];

        return [$column];
    }
}
