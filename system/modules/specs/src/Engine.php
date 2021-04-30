<?php

namespace Specs;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Engine
{
    /**
     * 待搜索的关键词
     *
     * @var string
     */
    protected $keywords = '';

    /**
     * 规格范围
     *
     * @var array
     */
    protected $specs = [];

    /**
     * 指定的记录
     * 格式：[spec_id => [record_id, ...], ...]
     *
     * @var array
     */
    protected $records = [];

    /**
     * 数据量
     *
     * @var int
     */
    protected $limit = -1;

    /**
     * 是否压缩
     *
     * @var bool
     */
    protected $compress = false;

    /**
     * 时间戳类型列
     *
     * @var array
     */
    protected $timestamps = ['created_at', 'updated_at'];

    public function __construct(?Request $request = null)
    {
        if ($request) {
            $this->initWithRequest($request);
        }
    }

    /**
     * @return static
     */
    public static function make(?Request $request = null)
    {
        return new static($request);
    }

    protected function initWithRequest(Request $request)
    {
        $this->keywords(urldecode($request->input('keywords')));

        $this->specs($request->input('specs'));

        $this->records($request->input('records'));

        $this->limit($request->input('limit') ?? -1);

        $this->compress($request->input('compress'));
    }

    public function keywords($keywords = null)
    {
        $this->keywords = strval($keywords ?? '');

        return $this;
    }

    public function specs($specs = null)
    {
        $this->specs = $this->normalizeList($specs);

        return $this;
    }

    public function records($records = null)
    {
        $this->records = $this->normalizeRecords($records);

        return $this;
    }

    public function limit($limit = null)
    {
        $this->limit = intval($limit ?? -1);

        return $this;
    }

    public function compress($compress = null)
    {
        $compress = $compress ?? false;

        $this->compress = $compress && !in_array($compress, ['false','off'], true);

        return $this;
    }

    /**
     * @param  string|null $keywords
     * @return array
     */
    public function search(?string $keywords = null)
    {
        if ($keywords) {
            $this->keywords($keywords);
        }

        return $this->get();
    }

    /**
     * @return array
     */
    public function get()
    {
        // Log::info($this->toArray());

        if ($this->records) {
            return $this->getRecords();
        }

        return $this->searchRecords();
    }

    /**
     * @return array
     */
    protected function getRecords()
    {
        // 获取可搜索的规格和字段
        $fields = $this->resolveSpecFields();

        $results = [];
        foreach (Spec::find(array_keys($this->records)) as $spec) {
            $attributes = $this->normalizeTimestamps($spec->attributesToArray());

            $records = DB::table($spec->getRecordsTable())->whereIn('id', $this->records[$attributes['id']])->get()
                ->map(function($record) {
                    return $this->normalizeTimestamps((array) $record);
                })->all();

            if ($this->compress) {
                $records = $this->compressRecords($records, $fields[$attributes['id']]['groupable'] ?? []);
            }

            $results[$attributes['id']] = compact('attributes', 'records');
        }

        return $results;
    }

    /**
     * @return array
     */
    protected function searchRecords()
    {
        // 识别形如『Label:keywords』的关键词
        // Label 代表某字段的标题，keywords 代表在该字段下待搜索的关键词
        [$label, $keywords] = $this->splitKeywords();

        // 获取可搜索的规格和字段
        $fields = $this->resolveSpecFields($label);

        $results = [];
        foreach (Spec::find(array_keys($fields)) as $spec) {
            $attributes = $this->normalizeTimestamps($spec->attributesToArray());
            $spec_id = $attributes['id'];

            $conditions = [];
            if ($this->keywords) {
                foreach ($fields[$spec_id]['searchable'] ?? [] as $field_id) {
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
                $records = $this->compressRecords($records, $fields[$spec_id]['groupable'] ?? []);
            }

            $results[$spec_id] = compact('attributes', 'records');
        }

        return $results;
    }

    /**
     * 获取指定记录列表
     *
     * @param  string|array|null $raw
     * @return array
     */
    protected function normalizeRecords($raw)
    {
        if (is_string($raw)) {
            $raw = $this->normalizeList($raw);
        }

        if (!is_array($raw) || empty($raw)) {
            return [];
        }

        $records = [];

        foreach ($raw as $value) {
            if (strpos($value, '/') > 0) {
                [$spec_id, $record_id] = explode('/', $value);
                $records[$spec_id][] = $record_id;
            } else {
                foreach ($this->specs as $spec_id) {
                    $records[$spec_id][] = $value;
                }
            }
        }

        return $records;
    }

    /**
     * 获取项目列表
     *  - 如果是字符串则以 , 分割为数组；
     *  - 如果是数组则检查每一项是否为空；
     *
     * @param  string|array|null $specs
     * @return array
     */
    protected function normalizeList($items)
    {
        if (is_string($items)) {
            $items = explode(',', $items);
        }

        $list = [];

        if (is_array($items)) {
            foreach ($items as $item) {
                $item = trim($item);
                if (strlen($item) > 0) {
                    $list[] = $item;
                }
            }
        }

        return $list;
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
            $fields = Arr::only($fields, array_values($this->specs));
        }

        return Arr::only($fields, $availableSpecs);
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

    public function toArray()
    {
        return [
            'keywords' => $this->keywords,
            'specs' => $this->specs,
            'records' => $this->records,
            'limit' => $this->limit,
            'compress' => $this->compress,
        ];
    }
}
