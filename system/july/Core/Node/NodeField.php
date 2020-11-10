<?php

namespace July\Core\Node;

use App\Utils\EventsBook;
use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use July\Core\Config\PathAliasStorage;
use July\Core\Config\PathViewStorage;
use July\Core\Entity\EntityBase;
use July\Core\EntityField\EntityFieldBase;
use July\Core\EntityField\EntityFieldStorage;
use July\Core\EntityField\FieldParameters;
use July\Core\EntityField\FieldType;

class NodeField extends EntityFieldBase
{
    /**
     * 可选字段
     */
    const SELECTABLE_FIELD = 0;

    /**
     * 预设字段
     */
    const PRESET_FIELD = 1;

    /**
     * 全局字段
     */
    const GLOBAL_FIELD = 2;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'node_fields';

    /**
     * 主键
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
     * 模型主键是否递增
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
        'field_type_id',
        // 'entity_name',
        'is_necessary',
        'is_searchable',
        'weight',
        'preset_type',
        'global_group',
        // 'delta',
        'label',
        'description',
        // 'langcode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_necessary' => 'boolean',
        'is_searchable' => 'boolean',
        'preset_type' => 'int',
        // 'delta' => 'int',
        'weight' => 'decimal:2',
    ];

    protected static $storages = [
        'url' => PathAliasStorage::class,
        'template' => PathViewStorage::class,
    ];

    // /**
    //  * {@inheritdoc}
    //  */
    // public static function getParentEntityClass()
    // {
    //     return Node::class;
    // }

    /**
     * 获取使用过当前字段的所有类型
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function nodeTypes()
    {
        return $this->belongsToMany(NodeType::class, 'node_field_node_type', 'node_field_id', 'node_type_id')
                    ->orderBy('node_field_node_type.delta')
                    ->withPivot([
                        'delta',
                        'weight',
                        'label',
                        'description',
                    ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage(EntityBase $entity = null)
    {
        $storage = static::$storages[$this->getKey()] ?? EntityFieldStorage::class;
        if ($entity) {
            $this->translateTo($entity->getLangcode());
        } else {
            $entity = new Node;
        }
        return new $storage($entity, $this);
    }

    /**
     * 获取所有全局字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getGlobalFields()
    {
        return static::query()->where('preset_type', static::GLOBAL_FIELD)->get();
    }

    /**
     * 获取所有字段的信息（包含参数）
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takeFieldsInfo()
    {
        $pocket = new Pocket(static::class);
        $key = 'all_fields_info';
        $event = static::class.'/'.$key.':created';

        if ($data = $pocket->get($key)) {
            $events = [
                static::class.':changed',
                FieldParameters::class.':changed',
            ];
            if (! events()->hasHappenedAfter($events, $event)) {
                return collect($data->value());
            }
        }

        $data = static::query()->with('parameters')->get()
            ->map(function(NodeField $field) {
                return $field->gather();
            })->all();

        $pocket->put($key, $data);

        events()->record($event);

        return collect($data);
    }

    /**
     * 获取所有全局字段的信息
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function takeGlobalFieldsInfo()
    {
        return NodeField::takeFieldsInfo()->groupBy('preset_type')->get(static::GLOBAL_FIELD);
    }

    /**
     * 获取所有预设字段的信息
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function takePresetFieldsInfo()
    {
        return NodeField::takeFieldsInfo()->groupBy('preset_type')->get(static::PRESET_FIELD);
    }

    /**
     * 获取所有非全局字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function takeSelectableFieldsInfo()
    {
        return NodeField::takeFieldsInfo()->groupBy('preset_type')->get(static::SELECTABLE_FIELD);
    }

    /**
     * 获取全局字段的构建材料
     *
     * @param  string $langcode
     * @return array
     */
    public static function takeGlobalFieldMaterials(string $langcode = null)
    {
        $langcode = $langcode ?? langcode('content');

        $pocket = new Pocket(static::class);
        $key = $pocket->key('global_field_materials/'.$langcode);

        if ($materials = $pocket->get($key)) {
            $materials = $materials->value();
        }

        $lastModified = last_modified(backend_path('template/components/'));
        if (!$materials || $materials['created_at'] < $lastModified) {
            $materials = [];
            foreach (static::takeGlobalFieldsInfo() as $field) {
                $materials[$field['id']] = FieldType::findOrFail($field['field_type_id'])->getMaterials($field);
            }
            $materials = [
                'created_at' => time(),
                'materials' => $materials,
            ];
            $pocket->put($key, $materials);
        }

        return $materials['materials'];
    }
}
