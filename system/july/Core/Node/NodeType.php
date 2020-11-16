<?php

namespace July\Core\Node;

use App\Utils\Pocket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use July\Core\Entity\EntityBundleBase;
use July\Core\EntityField\FieldType;
use July\Core\EntityField\FieldParameters;

class NodeType extends EntityBundleBase implements GetNodesInterface
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_types';

    /**
     * 主键名
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 主键类型
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 主键是否自增
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
        'id',
        'is_necessary',
        'label',
        'description',
        'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_necessary' => 'boolean',
    ];

    /**
     * 当前类型下的所有节点
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function nodes()
    {
        return $this->hasMany(Node::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        return $this->belongsToMany(NodeField::class, 'node_field_node_type', 'node_type_id', 'node_field_id')
            ->orderBy('node_field_node_type.delta')
            ->withPivot([
                'delta',
                'weight',
                'label',
                'description',
            ]);
    }

    /**
     * 统计使用次数
     *
     * @return array
     */
    public static function countReference()
    {
        $query = DB::table((new Node)->getTable())
            ->selectRaw('`node_type_id`, COUNT(*) as `total`')
            ->groupBy('node_type_id');

        return $query->pluck('total', 'node_type_id')->all();
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param  string|null $langcode
     * @return array
     */
    public function takeFieldMaterials(string $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        $pocket = new Pocket($this);
        $key = 'field_materials/'.$langcode;

        if ($materials = $pocket->get($key)) {
            $materials = $materials->value();
        }

        $modified = last_modified(backend_path('template/components/'));
        if (!$materials || $materials['created_at'] < $modified) {
            $materials = [];
            foreach ($this->fields as $field) {
                $materials[$field->id] = FieldType::findOrFail($field)->translateTo($langcode)->getMaterials();
            }

            $materials = [
                'created_at' => time(),
                'materials' => $materials,
            ];

            $pocket->put($key, $materials);
        }

        return $materials['materials'];
    }

    /**
     * 更新类型字段
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function updateFields(array $data)
    {
        // Log::info($data);
        $langcode = langcode('content');

        $pocket = new Pocket($this);

        $pocket->clear('field_ids');
        // $pocket->clear('fields_info/'.$langcode);

        // 清除碎片缓存
        // $this->cacheClear(['key'=>'fields', 'langcode'=>$langcode]);
        // $this->cacheClear(['key'=>'fieldJigsaws', 'langcode'=>$langcode]);

        DB::beginTransaction();

        $fields = [];
        $shared = [
            'entity_name' => Node::getEntityName(),
            'bundle_name' => NodeType::getEntityName(),
            'langcode' => $langcode,
        ];

        foreach ($data as $index => $field) {
            $fields[$field['id']] = [
                'delta' => $index,
                'weight' => $field['weight'] ?? 1,
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
            ];

            FieldParameters::updateOrCreate(
                ['field_id' => $field['id']] + $shared,
                ['parameters' => FieldType::findOrFail($field['field_type_id'])->extractParameters($field)]
            );
        }
        // Log::info($fields);

        $this->fields()->sync($fields);

        DB::commit();
    }

    public function get_nodes()
    {
        return NodeSet::make($this->nodes->keyBy('id')->all());
    }
}
