<?php

namespace Specs;

class Record
{
    /**
     * 检索规格数据
     *
     * @param  string|null $keywords 关键词
     * @return array
     */
    public static function search($keywords = null)
    {
        [$label, $keywords] = static::resolveKeywords($keywords);
        $specFields = static::resolveFields($label);

        $results = [];
        foreach ($specFields as $spec_id => $fields) {
            if ($spec = Spec::find($spec_id)) {
                $results = static::mergeResults($results, $spec->search($keywords, $fields));
            }
        }

        return $results;
    }

    /**
     * 拆分关键词，默认第一个 ':' 前为字段标签
     *
     * @param  string|null $keywords
     * @return array
     */
    protected static function resolveKeywords($keywords = null)
    {
        if (empty($keywords)) {
            return [null, null];
        }
        $keywords = trim($keywords);

        $label = null;
        $segments = explode(':', $keywords, 2);
        if (count($segments) > 1 && !empty($segments[0]) && !empty($segments[1])) {
            $label = trim($segments[0]);
            $keywords = trim($segments[1]);
        }

        return [$label, $keywords];
    }

    /**
     * 获取字段范围
     *
     * @param  string|null $label 字段标签
     * @return \Illuminate\Support\Collection
     */
    protected static function resolveFields(?string $label = null)
    {
        if (! $label) {
            return SpecField::all()->groupBy('spec_id');
        }

        $fields = SpecField::query()->where('label', $label)->get()->groupBy('spec_id');
        if ($fields->count()) {
            return $fields;
        }

        return SpecField::all()->groupBy('spec_id');
    }

    /**
     * 合并不同规格的搜索记录
     *
     * @param  array $data
     * @param  array $results
     * @return array
     */
    protected static function mergeResults($data, $results)
    {
        if (empty($data)) {
            return $results;
        }

        // 合并字段信息
        $fields = array_merge($data['fields'], $results['fields']);

        // 合并记录
        $records = array_merge($data['records'], $results['records']);

        // 合并分组信息
        $groups = $data['groups'];
        foreach ($results['groups'] as $key => $values) {
            if (!isset($groups[$key])) {
                $groups[$key] = $values;
                continue;
            }
            foreach ($values as $value => $count) {
                $groups[$key][$value] = ($groups[$key][$value] ?? 0) + $count;
            }
        }

        return compact('fields', 'groups', 'records');
    }
}
