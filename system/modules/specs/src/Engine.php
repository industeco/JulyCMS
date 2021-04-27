<?php

namespace Specs;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Engine
{
    /**
     * 待搜索的关键词
     *
     * @var string
     */
    protected $keywords;

    /**
     * 待搜索的关键词
     *
     * @var array
     */
    protected $specs;

    /**
     * 数据量
     *
     * @var int
     */
    protected $limit;

    /**
     * 是否压缩
     *
     * @var bool
     */
    protected $compress;

    /**
     * 时间戳类型列
     *
     * @var array
     */
    protected $timestamps = ['created_at', 'updated_at'];

    public function __construct($keywords = null, $specs = null, $limit = null, $compress = null)
    {
        $this->keywords = strval($keywords ?? urldecode(request('keywords')));

        $this->specs = $this->normalizeSpecs($specs ?? request('specs'));

        $this->limit = intval($limit ?? request('limit') ?? -1);

        $compress = $compress ?? request('compress');
        $this->compress = $compress && !in_array($compress, ['false','off'], true);
    }

    /**
     * @return static
     */
    public static function make($keywords = null, $specs = null, $limit = null, $compress = null)
    {
        return new static($keywords, $specs, $limit, $compress);
    }

    /**
     * @return array
     */
    public static function search($keywords = null, $specs = null, $limit = null, $compress = null)
    {
        return (new static($keywords, $specs, $limit, $compress))->getRecords();
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        // 识别形如『Label:keywords』的关键词
        // Label 代表某字段的标题，keywords 代表在该字段下待搜索的关键词
        [$label, $keywords] = $this->splitKeywords();

        // 获取可搜索的规格和字段
        $fields = $this->resolveSpecFields($label);

        $results = [];
        foreach (Spec::find(array_keys($fields)) as $spec) {
            $attributes = $this->normalizeTimestamps($spec->attributesToArray());

            $records = $this->getSpecRecords($spec, $fields[$attributes['id']] ?? []);

            $results[$attributes['id']] = compact('attributes', 'records');
        }

        return $results;
    }

    /**
     * 转规格 id 列表为数组
     *
     * @param  string|array|null $specs
     * @return array
     */
    public function normalizeSpecs($specs = null)
    {
        if (is_string($specs)) {
            $specs = explode(',', $specs);
        }

        if (is_array($specs)) {
            $list = [];
            foreach ($specs as $spec) {
                $spec = trim($spec);
                if (strlen($spec)) {
                    $list[] = $spec;
                }
            }
            return $list;
        }

        return [];
    }

    /**
     * 拆分关键词，默认第一个 ':' 前为字段标签
     *
     * @return array
     */
    public function splitKeywords()
    {
        if (empty($this->keywords)) {
            return [null, null];
        }

        if (false === strpos($this->keywords, ':')) {
            return [null, $this->keywords];
        }

        return explode(':', $this->keywords, 2);
    }

    /**
     * 获取字段范围
     *
     * @param  string|null $label 字段标签
     * @return array
     */
    public function resolveSpecFields(?string $label = null)
    {
        $fields = [];
        $availableSpecs = [];

        foreach (SpecField::all() as $field) {
            $field = $field->attributesToArray();
            $spec_id = $field['spec_id'];
            $field_id = $field['field_id'];

            if (($label && $field['label'] === $label) || (!$label && $field['is_searchable'])) {
                $fields[$spec_id]['searchable'][] = $field_id;
                $availableSpecs[] = $spec_id;
            }

            if ($field['is_groupable']) {
                $fields[$spec_id]['groupable'][] = $field_id;
            }
        }

        if (! empty($this->specs)) {
            $fields = Arr::only($fields, $this->specs);
        }

        return Arr::only($fields, $availableSpecs);
    }

    /**
     * 获取规格记录
     *
     * @param  \Specs\Spec $spec
     * @param  array $fields
     * @return array
     */
    public function getSpecRecords(Spec $spec, array $fields)
    {
        $conditions = [];
        if ($this->keywords) {
            foreach ($fields['searchable'] ?? [] as $field_id) {
                $conditions[] = [
                    $field_id, 'like', '%'.$this->keywords.'%', 'or'
                ];
            }
        }

        $records = DB::table($spec->getRecordsTable())->where($conditions)->limit($this->limit)->get()
            ->map(function($record) {
                return $this->normalizeTimestamps((array) $record);
            })->all();

        if ($this->compress) {
            $records = $this->compressRecords($records, $fields['groupable'] ?? []);
        }

        return $records;
    }

    /**
     * 将数组中的时间字段转换为时间戳形式（秒数）
     *
     * @param  array $record
     * @return array
     */
    public function normalizeTimestamps(array $record)
    {
        // 转换时间为秒数
        foreach ($this->timestamps as $column) {
            if ($value = $record[$column] ?? null) {
                $record[$column] = Carbon::make($value)->getTimestamp();
            }
        }

        return $record;
    }

    /**
     * 获取数据并压缩
     *
     * @param  array $records
     * @param  array $groups
     * @return array
     */
    public function compressRecords(array $records, array $groups)
    {
        $data = [];
        $columns = [];
        $values = [];

        $timebase = 0;
        $timestamps = $this->timestamps;

        foreach ($records as $record) {
            $record = (array) $record;

            // 保存列名
            if (! $columns) {
                $columns = array_keys($record);
            }

            // 压缩字段值
            foreach ($record as $key => $value) {
                if (in_array($key, $groups)) {
                    $record[$key] = $values[$value] ?? $values[$value] = count($values);
                }
            }

            // 压缩时间
            foreach ($timestamps as $column) {
                if ($time = $record[$column] ?? null) {
                    if (! $timebase) {
                        $timebase = $time;
                    }

                    $record[$column] = $time - $timebase;
                }
            }

            // 压缩键
            $data[] = array_values($record);
        }

        $values = array_keys($values);

        return [
            'data' => $data,
            'meta' => compact('columns','values','groups','timestamps','timebase'),
        ];
    }
}
