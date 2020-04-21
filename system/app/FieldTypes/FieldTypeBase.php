<?php

namespace App\FieldTypes;

use Illuminate\Support\Facades\Log;

/**
 * 模型字段类型定义类，简称定义类
 * 定义类主要用途：
 *  1. 辅助创建字段
 *  2. 构建字段数据表列
 *  3. 构建字段表单控件
 */
abstract class FieldTypeBase
{
    public static $isPublic = true;

    public static $title = '';

    public static $description = '';

    public static $searchable = true;

    /**
     * 当前类型字段生成字段数据表时的数据列信息，由多个 column 组成
     *
     * @param array $config
     * @return array
     */
    abstract public static function columns(array $config);

    /**
     * 将待存储的字段值转换为二维数组，数组每一行代表一条记录
     */
    public static function records($value, array $columns)
    {
        if (!strlen($value)) {
            return null;
        }
        return [
            [
                $columns[0]['name'] => $value,
            ],
        ];
    }

    /**
     * 将记录转换为值
     */
    public static function value(array $records, array $columns, array $config)
    {
        $column = $columns[0]['name'];
        return trim($records[0][$column]);
    }

    abstract public static function configStructure(): array;

    public static function config(array $data)
    {
        $lang = langcode();

        $config = [
            'langcode' => [
                'content_value' => $lang['content_value'],
                'interface_value' => $lang['interface_value'],
            ],
            'interface_values' => [],
            'content_values' => [],
        ];

        $structure = static::configStructure();

        foreach ($structure as $key => $type) {
            if ($key === 'interface_values' || $key === 'content_values') {
                $lang_key = trim($key, 's');
                foreach ($type as $k => $t) {
                    if (isset($data[$k])) {
                        $config[$key][$k] = [
                            $lang[$lang_key] => static::cast($data[$k], $t),
                        ];
                    }
                }
            } else {
                if (isset($data[$key])) {
                    $config[$key] = static::cast($data[$key], $type);
                }
            }
        }

        if (empty($config['interface_values'])) {
            unset($config['interface_values']);
        }

        if (empty($config['content_values'])) {
            unset($config['content_values']);
        }

        return $config;
    }

    /**
     *
     */
    abstract public static function parameters(array $data);

    abstract public static function element(array $parameters);

    public static function rules(array $parameters)
    {
        $rules = [];

        if ($parameters['required'] ?? false) {
            $rules[] = "{required:true, message:'不能为空', trigger:'submit'}";
        }

        $length = $parameters['length'] ?? 0;
        if ($length > 0) {
            $rules[] = "{max:{$length}, message: '最多 {$length} 个字符', trigger: 'change'}";
        }

        return $rules;
    }

    /**
     * 生成表单构建材料
     * @param array $data
     * @return array
     */
    public static function jigsaws(array $data)
    {
        $parameters = static::parameters($data);
        return [
            'truename' => $data['truename'],
            'value' => '',
            'element' => static::element($parameters),
            'rules' => static::rules($parameters),
        ];
    }

    public static function cast($value, $type)
    {
        switch ($type) {
            case 'string':
                return trim($value);

            case 'integer':
            case 'int':
                return intval($value);

            case 'boolean':
            case 'bool':
                return boolval($value);

            case 'array':
                $value = (array) $value;
                return array_filter($value);

            default:
                return $value;
        }
    }
}
