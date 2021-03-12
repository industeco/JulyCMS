<?php

namespace App\Utils;

class Rules
{
    /**
     * 默认的消息模板
     *
     * @var array
     */
    protected $messages = [
        'required' => '不能为空',
        'max' => '最多 {max} 个字符',
        'email' => '邮件格式不正确',
        'url' => '网址格式不正确',
        'pattern' => '格式不正确',
    ];

    /**
     * 解析字符串规则
     *
     * @param  string $rule
     * @return array
     */
    public static function normalize(string $rule)
    {
        $rule = explode(':', $rule);

        $name = $rule[0] ?? null;
        if (empty($name)) {
            return [null, null, null];
        }
        $name = trim($name);

        return static::$name($rule[1] ?? null, $rule[2] ?? null);
    }

    /**
     * required 规则
     *
     * @param  string|null $parameters
     * @param  string|null $message
     * @return array
     */
    public static function required(?string $parameters, ?string $message)
    {
        $message = $message ?? static::$messages['required'] ?? '';

        return ['required', '', $message];
    }

    /**
     * max 规则
     *
     * @param  string|null $parameters
     * @param  string|null $message
     * @return array
     */
    public static function max(?string $parameters, ?string $message)
    {
        $max = (int) $parameters;
        $message = $message ?? static::$messages['max'] ?? '';

        return ['max', $max, static::messageReplace($message, compact('max'))];
    }

    /**
     * email 规则
     *
     * @param  string|null $parameters
     * @param  string|null $message
     * @return array
     */
    public static function email(?string $parameters, ?string $message)
    {
        $message = $message ?? static::$messages['email'] ?? '';

        return ['type', 'email', $message];
    }

    /**
     * url 规则
     *
     * @param  string|null $parameters
     * @param  string|null $message
     * @return array
     */
    public static function url(?string $parameters, ?string $message)
    {
        $message = $message ?? static::$messages['url'] ?? '';

        return ['type', 'url', $message];
    }

    /**
     * pattern 规则
     *
     * @param  string|null $parameters
     * @param  string|null $message
     * @return array
     */
    public static function pattern(?string $parameters, ?string $message)
    {
        $message = $message ?? static::$messages['pattern'] ?? '';

        return ['pattern', $parameters, $message];
    }

    public static function messageReplace(string $message, array $parameters)
    {
        return preg_replace_callback('/\{.*?\}/', function ($maches) use ($parameters) {
            return $parameters[trim($maches[1])] ?? '{'.$maches[1].'}';
        }, $message);
    }

    public static function __callStatic($name, $arguments)
    {
        return [$name, $arguments[0] ?? '', $arguments[1] ?? ''];
    }
}
