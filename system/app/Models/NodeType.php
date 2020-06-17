<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Casts\Json;
use App\Contracts\GetNodes;
use App\Contracts\HasModelConfig;
use App\FieldTypes\FieldType;
use App\ModelCollections\NodeCollection;
use App\Traits\CastModelConfig;
use App\Traits\TruenameAsPrimaryKey;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class NodeType extends JulyModel implements GetNodes
{
    use TruenameAsPrimaryKey;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_types';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'is_preset',
        'label',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class, 'node_type');
    }

    public function fields()
    {
        return $this->belongsToMany(NodeField::class, 'node_field_node_type', 'node_type', 'node_field')
            ->orderBy('node_field_node_type.delta')
            ->withPivot([
                'delta',
                'weight',
                'label',
                'description',
            ]);
    }

    public static function usedByNodes()
    {
        $nodetypeUsed = [];
        $records = DB::select('SELECT `node_type`, count(`node_type`) as `total` FROM `nodes` GROUP BY `node_type`');
        foreach ($records as $record) {
            $nodetypeUsed[$record->node_type] = $record->total;
        }

        return $nodetypeUsed;
    }

    /**
     * 获取字段数据
     *
     * @param string|null $langcode
     * @return array
     */
    public function cacheGetFields($langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $cacheKey = $this->cacheKey('fields', compact('langcode'));

        if ($fields = $this->cacheGet($cacheKey)) {
            $fields = $fields['value'];
        } else {
            $fields = $this->fields->map(function($field) use($langcode) {
                return $field->gather($langcode);
            })->keyBy('truename')->all();
            $this->cachePut($cacheKey, $fields);
        }

        return $fields;
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param string|null $langcode
     * @param array $values
     * @return array
     */
    public function cacheGetFieldJigsaws($langcode = null, array $values = [])
    {
        $langcode = $langcode ?: langcode('content');
        $cacheKey = $this->cacheKey('fieldJigsaws', compact('langcode'));

        if ($jigsaws = $this->cacheGet($cacheKey)) {
            $jigsaws = $jigsaws['value'];
        }

        $lastModified = last_modified(background_path('template/components/'));
        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach ($this->cacheGetFields($langcode) as $field) {
                $jigsaws[$field['truename']] = FieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            $this->cachePut($cacheKey, $jigsaws);
        }

        $jigsaws = $jigsaws['jigsaws'];
        if ($values) {
            foreach ($jigsaws as $fieldName => &$jigsaw) {
                $jigsaw['value'] = $values[$fieldName] ?? null;
            }
        }

        return $jigsaws;
    }

    /**
     * 更新类型字段
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function updateFields(array $fields)
    {
        $langcode = langcode('content');

        // 清除碎片缓存
        $this->cacheClear($this->cacheKey('fields', compact('langcode')));
        $this->cacheClear($this->cacheKey('fieldJigsaws', compact('langcode')));

        DB::beginTransaction();

        $fields = [];
        foreach ($fields as $index => $field) {
            $fields[$field['truename']] = [
                'delta' => $index,
                'weight' => $field['weight'] ?? 1,
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
            ];

            FieldParameters::updateOrCreate([
                'keyname' => implode('.', ['node_field', $field['truename'], 'node_type', $this->getKey(), $langcode]),
            ], ['data' => FieldType::extractParameters($field)]);
        }

        $this->fields()->sync($fields);

        DB::commit();
    }

    public function get_nodes(): NodeCollection
    {
        return NodeCollection::make($this->nodes->keyBy('id')->all());
    }
}
