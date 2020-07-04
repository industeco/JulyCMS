<?php

namespace App\Entity\FieldTypes;

class HtmlField extends BaseFieldType
{
    /**
     * {@inheritDoc}
     */
    public static function label(): string
    {
        return 'HTML';
    }

    /**
     * {@inheritDoc}
     */
    public static function description(): ?string
    {
        return '适用于 HTML 文档';
    }

    /**
     * {@inheritDoc}
     */
    public function getSchema(): array
    {
        $schema = parent::getSchema();

        return array_merge($schema, [
            'helptext' => [
                'value_type' => 'string',
            ],
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getColumns($fieldName = null, ?array $parameters = []): array
    {
        $fieldName = $fieldName ?? $this->field->getKey();
        $column = [
            'type' => 'text',
            'name' => $fieldName.'_value',
            'parameters' => [],
        ];

        return [$column];
    }
}
