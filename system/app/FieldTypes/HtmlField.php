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
            'required' => 'boolean',
            'index_weight' => 'integer',
            'interface_values' => [
                'label' => 'string',
                'help' => 'string',
                'description' => 'string',
            ],
        ];
    }

    public static function parameters(array $data)
    {
        return array_merge([
            'help' => '',
        ], describe($data));
    }

    public static function element(array $parameters)
    {
        return view('admin::components.html', $parameters)->render();
    }
}
