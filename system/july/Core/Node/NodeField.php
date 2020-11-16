<?php

namespace July\Core\Node;

use App\Utils\EventsBook;
use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use July\Core\Config\PathAliasAccessor;
use July\Core\Config\PartialViewAccessor;
use July\Core\Entity\EntityBase;
use July\Core\EntityField\EntityFieldBase;
use July\Core\EntityField\FieldAccessor;
use July\Core\EntityField\FieldParameters;
use July\Core\EntityField\FieldType;
use July\Core\Taxonomy\TagsAccessor;

class NodeField extends EntityFieldBase
{
    /**
     * 宿主实体的实体名
     */
    protected static $hostEntityName = 'node';

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
        'is_necessary',
        'is_searchable',
        'weight',
        'preset_type',
        'global_group',
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
        'weight' => 'decimal:2',
    ];

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
        $key = 'field_infos';
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

        $data = static::query()->with('fieldParameters')->get()
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
     * @return \Illuminate\Support\Collection
     */
    public static function takeGlobalFieldsInfo()
    {
        return static::takeFieldsInfo()->groupBy('preset_type')->get(static::GLOBAL_FIELD);
    }

    /**
     * 获取所有预设字段的信息
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takePresetFieldsInfo()
    {
        return static::takeFieldsInfo()->groupBy('preset_type')->get(static::PRESET_FIELD);
    }

    /**
     * 获取所有非全局字段
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takeSelectableFieldsInfo()
    {
        return static::takeFieldsInfo()->groupBy('preset_type')->get(static::SELECTABLE_FIELD);
    }

    /**
     * 获取全局字段的构建材料
     *
     * @param  string|null $langcode
     * @return array
     */
    public static function takeGlobalFieldMaterials(?string $langcode = null)
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
