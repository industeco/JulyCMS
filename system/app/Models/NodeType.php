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

class NodeType extends JulyModel implements GetNodes, HasModelConfig
{
    use CastModelConfig;

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
        // 'langcode',
        'config',
        // 'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'config' => Json::class,
    ];

    /**
     * 哪些字段可更新（白名单）
     *
     * @var array
     */
    protected $updateOnly = [
        'config',
    ];

    public function nodes()
    {
        return $this->hasMany(Node::class, 'node_type');
    }

    public function fields()
    {
        return $this->belongsToMany(NodeField::class, 'node_field_node_type', 'node_type', 'node_field')
                ->using(NodeTypeNodeField::class)
                ->orderBy('node_field_node_type.delta')
                ->withPivot(
                    'delta',
                    'config'
                );
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

    public function configStructure(): array
    {
        return [
            'name' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
            'description' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
        ];
    }

    public function retrieveFields($langcode = null)
    {
        $langcode = $langcode ?? langcode('admin_page');

        $cacheid = $this->truename.'/fields';
        if ($fields = $this->cacheGet($cacheid, $langcode)) {
            $fields = $fields['value'];
        } else {
            $fields = [];
            foreach ($this->fields as $field) {
                $config = extract_config(
                    array_replace_recursive($field->config, $field->pivot['config']),
                    $field->configStructure()
                );
                $field = array_merge($field->toArray(), $config);
                $field['delta'] = $field['pivot']['delta'];
                unset($field['pivot']);
                unset($field['config']);
                $fields[] = $field;

                // $field = $field->toArray();
                // $field['delta'] = $field['pivot']['delta'];
                // $field['config'] = array_replace_recursive($field['config'], $field['pivot']['config']);
                // unset($field['pivot']);
                // $fields[] = $field;
            }
            $this->cachePut($cacheid, $fields, $langcode);
        }

        return $fields;
    }

    public function retrieveFieldJigsaws($langcode = null, array $values = null)
    {
        $langcode = $langcode ?? langcode('admin_page');

        $lastModified = last_modified(view_path('components/'));

        $cacheid = $this->truename.'/fieldJigsaws';
        if ($jigsaws = $this->cacheGet($cacheid, $langcode)) {
            $jigsaws = $jigsaws['value'];
        }

        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach ($this->retrieveFields() as $field) {
                $jigsaws[$field['truename']] = FieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            $this->cachePut($cacheid, $jigsaws, $langcode);
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
        $langcode = langcode('admin_page');

        // 清除碎片缓存
        $this->cacheClear($this->truename.'/fields', $langcode);
        $this->cacheClear($this->truename.'/fieldJigsaws', $langcode);

        $node_type = $this->truename;
        $fields = [];
        foreach ($request->input('fields', []) as $index => $field) {
            $fields[] = [
                'node_type' => $node_type,
                'node_field' => $field['truename'],
                'delta' => $index,
                'config' => json_encode(FieldType::buildConfig($field), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
            ];
        }

        DB::table('node_field_node_type')->where('node_type', $node_type)->delete();
        DB::transaction(function() use ($fields) {
            DB::table('node_field_node_type')->insert($fields);
        });
    }

    // /**
    //  * 保存前对请求数据进行处理
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param \App\Models\NodeType $nodeType
    //  * @return Array
    //  */
    // public static function prepareRequest(Request $request, NodeType $nodeType = null)
    // {
    //     $config = static::buildConfig($request->all());
    //     if ($nodeType) {
    //         unset($config['langcode']);
    //         return [
    //             'config' => array_replace_recursive($nodeType->config, $config),
    //         ];
    //     }

    //     return [
    //         'truename' => $request->input('truename'),
    //         'config' => $config,
    //     ];
    // }

    public function get_nodes(): NodeCollection
    {
        $ids = $this->nodes()->pluck('id')->all();
        return NodeCollection::find($ids);
    }
}
