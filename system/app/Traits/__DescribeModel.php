<?php

namespace App\Traits;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait DescribeModel
{
    public static function describe($records, $lang)
    {
        if (is_array($records) || $records instanceof Collection) {
            $data = [];
            foreach ($records as $record) {
                if ($item = static::describeData($record, $lang)) {
                    $data[] = $item;
                }
            }
            return $data;
        }

        return static::describeData($records, $lang) ?? [];
    }

    /**
     * @param Model|array $model
     * @param String $lang
     */
    public static function describeData($data, $lang)
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }
        if (! is_array($data)) {
            return null;
        }

        if ($config = $data['config'] ?? null) {
            if ($parameters = $config['parameters'] ?? null) {
                foreach ($parameters as $key => $value) {
                    $data[$key] = $value;
                }
            }

            if ($rules = $config['rules'] ?? null) {
                foreach ($rules as $key => $value) {
                    $data[$key] = $value;
                }
            }

            if ($descriptors = $config['descriptors'] ?? null) {
                $langcode = $data['langcode'];
                foreach ($descriptors as $key => $value) {
                    $data[$key] = $value[$lang] ?? $value[$langcode];
                }
            }

            unset($data['config']);
        }

        return $data;
    }
}
