<?php

namespace App\Utils;

use Illuminate\Support\Arr as SupportArr;

class Arr extends SupportArr
{
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

    // /**
    //  * 将值转为数组
    //  *
    //  * @param mixed $item
    //  * @return array
    //  */
    // public static function of($item)
    // {
    //     if (is_array($item)) {
    //         return $item;
    //     } elseif (is_json($item)) {
    //         return json_decode($item, true);
    //     } elseif ($item instanceof Model) {
    //         return $item->attributesToArray();
    //     } elseif ($item instanceof Enumerable) {
    //         return $item->all();
    //     } elseif ($item instanceof Arrayable) {
    //         return $item->toArray();
    //     } elseif ($item instanceof Jsonable) {
    //         return json_decode($item->toJson(), true);
    //     } elseif ($item instanceof JsonSerializable) {
    //         return (array) $item->jsonSerialize();
    //     } elseif ($item instanceof Traversable) {
    //         return iterator_to_array($item);
    //     }

    //     return (array) $item;
    // }

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
