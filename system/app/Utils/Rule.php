<?php

namespace App\Utils;

class Rule
{
    /**
     * 补全规则
     *
     * @param  string $rule
     * @return array
     */
    public static function normalize(string $rule)
    {
        return static::performNormalize(...explode($rule, ':'));
    }

    public static function performNormalize(string $name = '', string $params = '', string $message = '')
    {
        $name = trim($name);
        if (! $name) {
            return [$name, $params, $message];
        }

        return static::$name($params, $message);
    }

    public static function required(string $params, string $message)
    {
        return ['required', '', $message ?: '不能为空'];
    }

    public static function max(string $params, string $message)
    {
        $params = (int) $params;

        return ['max', $params, $message ?: "最多 {$params} 个字符"];
    }

    public static function email(string $params, string $message)
    {
        return ['type', 'email', $message ?: "格式不正确"];
    }

    public static function url(string $params, string $message)
    {
        return ['type', 'url', $message ?: "格式不正确"];
    }

    public static function pattern(string $params, string $message)
    {
        return ['pattern', $params, $message ?: "格式不正确"];
    }

    public static function __callStatic($name, $arguments)
    {
        return [$name, $arguments[0] ?? '', $arguments[1] ?? ''];
    }
}
