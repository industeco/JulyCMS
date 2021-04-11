<?php

namespace App\Support;

use Illuminate\Support\Arr as SupportArr;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Enumerable;
use InvalidArgumentException;
use JsonSerializable;
use Traversable;

class Arr extends SupportArr
{
    /**
     * Get one or a specified number of random values from an array.
     *
     * @param  array  $array
     * @param  int|null  $number
     * @param  bool  $withKeys
     * @return mixed
     *
     * @throws \InvalidArgumentException
     */
    public static function random($array, $number = null, $withKeys = false)
    {
        $requested = is_null($number) ? 1 : $number;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($number)) {
            return $array[array_rand($array)];
        }

        if ((int) $number === 0) {
            return [];
        }

        $keys = array_rand($array, $number);

        $results = [];

        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        if ($withKeys) {
            $results = array_combine((array) $keys, $results);
        }

        return $results;
    }

    /**
     * 按键名筛选数组，如果指定了别名，则重命名该键
     *
     * @param  array  $array
     * @param  array  $keys
     * @return array
     */
    public static function selectAs(array $array, array $keys)
    {
        if (! static::isAssoc($keys)) {
            return static::only($array, $keys);
        }

        $results = [];
        foreach ($keys as $key => $alias) {
            if (is_int($key)) {
                $key = $alias;
            }
            if (array_key_exists($key, $array)) {
                $results[$alias] = $array[$key];
            }
        }
        return $results;
    }

    /**
     * 将值转为数组
     *
     * @param mixed $items
     * @return array
     */
    public static function from($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Enumerable) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    // /**
    //  * 增强版 pluck，截取多列
    //  *
    //  * @param iterable $items 二维数组或可转为二维数组的对象
    //  * @param array $columns 列名
    //  * @param string|callable|null $key 键名
    //  * @param boolean $except 排除模式
    //  * @return array;
    //  */
    // public static function pluckColumns($items, array $columns = [], $key = null, $except = false)
    // {
    //     $results = [];

    //     foreach ($items as $item) {
    //         $item = static::of($item);
    //         if ($columns) {
    //             if ($except) {
    //                 $item = static::except($item, $columns);
    //             } else {
    //                 $item = static::only($item, $columns);
    //             }
    //         }
    //         if (is_null($key)) {
    //             $results[] = $item;
    //         } else {
    //             $itemKey = $item[$key] ?? null;

    //             if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
    //                 $itemKey = (string) $itemKey;
    //             }

    //             $results[$itemKey] = $item;
    //         }
    //     }

    //     return $results;
    // }

    // /**
    //  * 排除指定列
    //  *
    //  * @param iterable $items 二维数组或可转为二维数组的对象
    //  * @param array $columns 列名
    //  * @param string|callable|null $key 键名
    //  * @return array;
    //  */
    // public static function pluckColumnsExcept($items, array $columns = [], $key = null)
    // {
    //     return static::pluckColumns($items, $columns, $key, true);
    // }

    // /**
    //  * 使用指定分隔符为数组降维
    //  *
    //  * @param  iterable  $array
    //  * @param  string  $prepend
    //  * @return array
    //  */
    // public static function dot($array, $prepend = '', $glue = '.')
    // {
    //     $results = [];

    //     foreach ($array as $key => $value) {
    //         if (is_array($value) && ! empty($value)) {
    //             $results = array_merge($results, static::dot($value, $prepend.$key.$glue, $glue));
    //         } else {
    //             $results[$prepend.$key] = $value;
    //         }
    //     }

    //     return $results;
    // }
}
