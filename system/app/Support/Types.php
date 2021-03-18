<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Date;

class Types
{
    /**
     * 默认支持的转换类型
     *
     * @var array
     */
    protected static $primitiveCastTypes = [
        'array',
        'bool',
        'boolean',
        'collection',
        'date',
        'datetime',
        'decimal',
        'double',
        'float',
        'int',
        'integer',
        'json',
        'object',
        'real',
        'string',
        'timestamp',
    ];

    /**
     * 转换一个值为目标类型
     *
     * @param  mixed  $value 待转换的值
     * @param  string  $caster 转换器
     * @param  bool  $force 是否对 null 作强制转换
     * @return mixed
     */
    public static function cast($value, string $caster, $force = true)
    {
        [$castType, $parameters] = static::parseCaster($caster);

        if (is_subclass_of($castType, CastsAttributes::class)) {
            return (new $castType(...$parameters))->get(null, null, $value, []);
        }

        $castType = strtolower($castType);
        if (!in_array($castType, static::$primitiveCastTypes) || (is_null($value) && !$force)) {
            return $value;
        }

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return static::asFloat($value, $parameters);
            case 'decimal':
                return static::asDecimal($value, $parameters);
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'object':
                return static::asObject($value, $parameters);
            case 'array':
            case 'json':
                return static::asArray($value, $parameters);
            case 'collection':
                return static::asCollection($value, $parameters);
            case 'date':
                return static::asDate($value, $parameters);
            case 'datetime':
                return static::asDateTime($value, $parameters);
            case 'timestamp':
                return static::asTimestamp($value, $parameters);
        }

        return $value;
    }

    /**
     * 解析转换器，拆分为两部分：
     *  - castType：转换类型
     *  - parameters：参数
     *
     * @return array
     */
    protected static function parseCaster(string $caster)
    {
        $castType = $caster;
        $parameters = [];

        if (strpos($castType, ':') !== false) {
            $segments = explode(':', $castType, 2);

            $castType = $segments[0];
            $parameters = explode(',', $segments[1]);
        }

        return [trim($castType), $parameters];
    }

    /**
     * 转为浮点值
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected static function asFloat($value)
    {
        switch ((string) $value) {
            case 'Infinity':
                return INF;
            case '-Infinity':
                return -INF;
            case 'NaN':
                return NAN;
            default:
                return (float) $value;
        }
    }

    /**
     * 转为指定位数的小数
     *
     * @param  float  $value
     * @param  array  $parameters 转换参数
     * @return string
     */
    protected static function asDecimal($value, array $parameters)
    {
        $decimals = intval($parameters[0] ?? 0);
        return number_format($value, $decimals, '.', '');
    }

    /**
     * 转 JSON 为 array
     *
     * @param  string  $value
     * @return mixed
     */
    protected static function asArray($value)
    {
        return json_decode($value, true);
    }

    /**
     * 转 JSON 为 object
     *
     * @param  string  $value
     * @return mixed
     */
    protected static function asObject($value)
    {
        return json_decode($value);
    }

    /**
     * 转 JSON 为 array
     *
     * @param  string  $value
     * @param  array  $parameters 转换参数
     * @return mixed
     */
    protected static function asCollection($value, array $parameters)
    {
        return new BaseCollection(static::asArray($value, $parameters));
    }

    /**
     * Return a timestamp as DateTime object with time set to 00:00:00.
     *
     * @param  mixed  $value
     * @param  array  $parameters 转换参数
     * @return \Illuminate\Support\Carbon
     */
    protected static function asDate($value, array $parameters)
    {
        return static::asDateTime($value, $parameters)->startOfDay();
    }

    /**
     * Return a timestamp as unix timestamp.
     *
     * @param  mixed  $value
     * @param  array  $parameters 转换参数
     * @return int
     */
    protected static function asTimestamp($value, array $parameters)
    {
        return static::asDateTime($value, $parameters)->getTimestamp();
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @param  array  $parameters 转换参数
     * @return \Illuminate\Support\Carbon
     */
    protected static function asDateTime($value, array $parameters)
    {
        // If this value is already a Carbon instance, we shall just return it as is.
        // This prevents us having to re-instantiate a Carbon instance when we know
        // it already is one, which wouldn't be fulfilled by the DateTime check.
        if ($value instanceof CarbonInterface) {
            return Date::instance($value);
        }

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTimeInterface) {
            return Date::parse(
                $value->format('Y-m-d H:i:s.u'), $value->getTimezone()
            );
        }

        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Date::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        if (static::isStandardDateFormat($value)) {
            return Date::instance(Carbon::createFromFormat('Y-m-d', $value)->startOfDay());
        }

        $format = $parameters[0] ?? 'Y-m-d H:i:s';

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        if (Date::hasFormat($value, $format)) {
            return Date::createFromFormat($format, $value);
        }

        return Date::parse($value);
    }

    /**
     * Determine if the given value is a standard date format.
     *
     * @param  string  $value
     * @return bool
     */
    protected static function isStandardDateFormat($value)
    {
        return preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $value);
    }
}
