<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\View;

class HtmlField extends FieldTypeBase
{
    public static $title = 'HTML';

    public static $description = '适用于 HTML 文档';

    public static function columns(array $config)
    {
        $column = [
            'type' => 'text',
        ];
        return [$column];
    }

    public static function configStructure(): array
    {
        return [
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
        ];
    }

    public static function element(array $parameters)
    {
        return view('admin::components.html', $parameters)->render();
    }
}
