<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\View;

class HtmlField extends FieldTypeBase
{
    /**
     * {@inheritdoc}
     */
    public static function getAlias(): string
    {
        return 'html';
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabel(): string
    {
        return 'HTML';
    }

    /**
     * {@inheritdoc}
     */
    public static function getDescription(): string
    {
        return '适用于 HTML 文档';
    }

    public function getColumns($fieldName, array $parameters = []): array
    {
        $column = [
            'type' => 'text',
            'name' => $fieldName.'_value',
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
            'helptext' => [
                'type' => 'string',
            ],
        ];
    }

    public function getElement(array $fieldData)
    {
        return view('admin::components.html', $fieldData)->render();
    }
}
