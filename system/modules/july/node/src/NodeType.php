<?php

namespace July\Node;

use App\Utils\Pocket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Entity\EntityBundleBase;
use App\EntityField\FieldType;
use App\EntityField\FieldParameters;
use App\EntityField\FieldTypes\FieldTypeManager;
use App\Models\ModelBase;

class NodeType extends ModelBase implements GetNodesInterface
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
        'label',
        'description',
        'langcode',
        'is_reserved',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_reserved' => 'boolean',
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
                // 'weight',
                'label',
                'description',
            ]);
    }

    /**
     * 引用计数
     *
     * @return array
     */
    public static function referencedByNode()
    {
        return Node::query()->selectRaw('`node_type_id`, COUNT(*) as `total`')
            ->groupBy('node_type_id')
            ->pluck('total', 'node_type_id')->all();
    }

    /**
     * 获取模型列表数据
     *
     * @return array
     */
    public static function index()
    {
        // 统计每个类型被节点引用次数（也就是有多少个节点使用该类型）
        $referenced = static::referencedByNode();

        // 获取模型列表
        $nodeTypes = parent::index();

        // 补充引用计数
        foreach ($nodeTypes as $id => &$nodeType) {
            $nodeType['referenced'] = $referenced[$id] ?? 0;
        }

        return $nodeTypes;
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param  string|null $langcode
     * @return array
     */
    public function retrieveFieldMaterials(string $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');

        $pocket = new Pocket($this, 'field_materials');

        if ($materials = $pocket->get()) {
            $materials = $materials->value();
        }

        $modified = last_modified(backend_path('template/components/'));
        if (!$materials || $materials['created_at'] < $modified) {
            $materials = [];
            foreach ($this->fields as $field) {
                $materials[$field->id] = FieldTypeManager::findOrFail($field->translateTo($langcode))->getMaterials();
            }

            $materials = [
                'created_at' => time(),
                'materials' => $materials,
            ];

            $pocket->put($materials);
        }

        return $materials['materials'];
    }

    /**
     * 更新类型字段
     *
     * @param  \Illuminate\Http\Request $request
     * @return void
     */
    public function updateRelatedFields(array $data)
    {
        // Log::info($data);
        $langcode = langcode('content');

        Pocket::make($this, 'field_ids')->clear();

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
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
                'is_required' => boolval($field['is_required'] ?? false),
                'helpertext' => $field['helpertext'] ?? null,
            ];

            FieldParameters::updateOrCreate(
                ['field_id' => $field['id']] + $shared,
                ['parameters' => FieldTypeManager::findOrFail($field['field_type_id'])->extractParameters($field)]
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
