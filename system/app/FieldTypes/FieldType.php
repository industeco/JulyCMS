<?php

namespace App\FieldTypes;

use Error;

class FieldType
{
    /**
     * 模型字段类型与定义类对应表
     */
    protected static $types = [
        // 纯文字
        'text' => TextField::class,

        // HTML
        'html' => HtmlField::class,

        // 文件名
        'file' => FileField::class,
    ];

    public static function getTypes()
    {
        $types = [];
        foreach (static::$types as $typeName => $fieldType) {
            $types[$typeName] = [
                'name' => $typeName,
                'title' => $fieldType::$title,
                'description' => $fieldType::$description,
                'searchable' => $fieldType::$searchable,
            ];
        }

        return $types;
    }

    /**
     * 获取定义类实例
     *
     * @param string $alias 定义类别名
     * @return \App\FieldTypes\FieldTypeBase|null
     */
    public static function find($alias)
    {
        if (($type = static::$types[$alias] ?? null) && $type::$isPublic) {
            return $type;
        }
        return null;
    }

    /**
     * 获取定义类实例，失败则抛出错误
     *
     * @param string $alias 定义类别名
     * @return \App\FieldTypes\FieldTypeBase
     */
    public static function findOrFail($alias)
    {
        if ($class = static::find($alias)) {
            return $class;
        }
        throw new Error('找不到 ['.$alias.'] 对应的字段类型。');
    }

    public static function getColumns($type, array $config)
    {
        if ($fieldType = static::find($type)) {
            return $fieldType::columns($config);
        }
        return [];
    }

    public static function getRecords($type, $value, array $columns)
    {
        $fieldType = static::findOrFail($type);
        return $fieldType::records($value, $columns);
    }

    public static function getValue($type, array $records, array $columns, array $config)
    {
        $fieldType = static::findOrFail($type);
        return $fieldType::value($records, $columns, $config);
    }

    public static function getConfig(array $data)
    {
        $fieldType = static::findOrFail($data['field_type'] ?? null);
        return $fieldType::config($data);
    }

    public static function getJigsaws(array $data)
    {
        $fieldType = static::findOrFail($data['field_type'] ?? null);
        $jigsaws = $fieldType::jigsaws($data);
        $jigsaws['type'] = $data['field_type'];
        return $jigsaws;
    }

    // /**
    //  * 获取分组的类型列表
    //  *
    //  * @return array
    //  */
    // public static function getGroupedOptions()
    // {
    //     if ($options = Cache::get('field_type_options_grouped')) {
    //         return $options;
    //     }

    //     $options = [];
    //     $groupDescs = [];
    //     foreach (static::$types as $key => $type) {
    //         $type = new $type;
    //         $group = $type->groupName;
    //         if (! isset($groupDescs[$group])) {
    //             $groupDescs[$group] = $type->groupDesc;
    //         }
    //         $options[$key] = [
    //             'alias' => $key,
    //             'title' => $type->title,
    //             'group' => $group,
    //         ];
    //     }

    //     $groupedOptions = collect($options)->groupBy('group', true)
    //         ->map(function($types, $group) use ($groupDescs) {
    //             return [
    //                 'label' => $groupDescs[$group] ?? $group,
    //                 'options' => $types->pluck('title', 'alias')->toArray(),
    //             ];
    //         })->toArray();

    //     Cache::put('field_type_options_grouped', $groupedOptions);

    //     return $groupedOptions;
    // }

    // /**
    //  * 生成表单控件，节点创建/编辑表单，或节点表单的内嵌表单
    //  *
    //  * @param array $data
    //  * @param \App\Models\Node $node 如果是节点编辑表单的控件，则需要传递当前节点
    //  * @return array
    //  */
    // public static function makeFormField(array $data, Node $node = null)
    // {
    //     if (!isset($data['label'])) {
    //         $data['label'] = $data['name'] ?? str_replace(['_', '-'], '', Str::title($data['real_name']));
    //     }
    //     if (isset($data['real_name'])) {
    //         $data['name'] = $data['real_name'];
    //     }
    //     if ($node) {
    //         $data['node_id'] = $node->id;
    //     }

    //     // if (!($data['invoke'] ?? null)) {
    //     //     $data['invoke'] = $data['name'];
    //     // }

    //     $limit = intval($data['amount'] ?? 1);
    //     if ($limit !== 1 && $data['type'] !== 'table') {
    //         $data['amount'] = 1;
    //         $data['columns'] = [$data];
    //         $data['type'] = 'table';
    //     }

    //     $type = static::findTypeOrFail($data['type']);
    //     return $type->makeFormField($data);
    // }
}
