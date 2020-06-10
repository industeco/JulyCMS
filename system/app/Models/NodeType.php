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

class NodeType extends JulyModel implements GetNodes
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_types';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'truename';

    /**
     * 主键“类型”。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 指示模型主键是否递增
     *
     * @var bool
     */
    public $incrementing = false;

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

    public function fields($langcode = null)
    {
        return $this->belongsToMany(NodeField::class, 'node_field_node_type', 'node_type', 'node_field')
            ->using(NodeTypeNodeField::class)
            ->orderBy('node_field_node_type.delta')
            ->withPivot([
                'delta',
                'weight',
                'label',
                'description',
                'langcode',
            ]);
    }

    public static function countByNodeField()
    {
        $types = [];
        $records = DB::select('SELECT `node_field`, count(`node_field`) as `total` FROM `node_field_node_type` GROUP BY `node_field`');
        foreach ($records as $record) {
            $types[$record->node_type] = $record->total;
        }

        return $types;
    }

    /**
     * 获取字段数据
     *
     * @param string|null $langcode
     * @return array
     */
    public function retrieveFields($langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $cacheid = $this->truename.'/fields';
        if ($fields = static::cacheGet($cacheid, $langcode)) {
            $fields = $fields['value'];
        } else {
            $fields = $this->fields->map(function($field) use($langcode) {
                return $field->gather($langcode);
            })->keyBy('truename')->all();
            static::cachePut($cacheid, $fields, $langcode);
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
    public function retrieveFieldJigsaws($langcode = null, array $values = null)
    {
        $langcode = $langcode ?: langcode('content');
        $lastModified = last_modified(view_path('components/'));
        $cacheid = $this->truename.'/fieldJigsaws';
        if ($jigsaws = static::cacheGet($cacheid, $langcode)) {
            $jigsaws = $jigsaws['value'];
        }

        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach ($this->retrieveFields($langcode) as $field) {
                $jigsaws[$field['truename']] = FieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            static::cachePut($cacheid, $jigsaws, $langcode);
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
    public function updateFields(Request $request)
    {
        $langcode = langcode('content');

        // 清除碎片缓存
        static::cacheClear($this->truename.'/fields', $langcode);
        static::cacheClear($this->truename.'/fieldJigsaws', $langcode);

        DB::beginTransaction();

        $fields = [];
        foreach ($request->input('fields', []) as $index => $field) {
            $fields[$field['truename']] = [
                'delta' => $index,
                'weight' => $field['weight'] ?? 1,
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
                'langcode' => $langcode,
            ];

            FieldParameters::updateOrCreate([
                'keyname' => implode('.', ['node_field', $field['truename'], 'node_type', $this->truename]),
                'langcode' => $langcode,
            ], ['data' => FieldType::pickParameters($field)]);
        }

        $this->fields()->sync($fields);

        DB::commit();
    }

    public function get_nodes(): NodeCollection
    {
        $ids = $this->nodes()->pluck('id')->all();
        return NodeCollection::find($ids);
    }
}
