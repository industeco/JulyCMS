<?php

namespace July\Core\Node;

use App\Utils\Pocket;
use Illuminate\Support\Facades\Log;
use July\Core\Entity\EntityBase;
use July\Core\EntityField\EntityFieldBase;
use July\Core\EntityField\FieldParameters;
use July\Core\EntityField\FieldType;

class NodeField extends EntityFieldBase
{
    const PRESET_TYPE = [
        'normal' => 0,
        'preset' => 1,
        'global' => 2,
    ];

    /**
     * 宿主实体的实体名
     */
    protected static $hostEntityName = 'node';

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
     * 内建属性登记处
     *
     * @var array
     */
    protected static $columns = [
        'id',
        'field_type_id',
        'is_necessary',
        'is_searchable',
        'weight',
        'preset_type',
        'global_group',
        'label',
        'description',
        'created_at',
        'updated_at',
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
                        // 'weight',
                        'label',
                        'description',
                    ]);
    }

    /**
     * 将预设类型转换为文字
     *
     * @param  string|int
     * @return string
     */
    public function getPresetTypeAttribute($presetType)
    {
        return array_flip(static::PRESET_TYPE)[$presetType] ?? 'normal';
    }

    /**
     * 限定仅查询常规字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNormalFields($query)
    {
        return $query->where('preset_type', static::PRESET_TYPE['normal']);
    }

    /**
     * 限定仅查询预设字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePresetFields($query)
    {
        return $query->where('preset_type', static::PRESET_TYPE['preset']);
    }

    /**
     * 限定仅查询全局预设字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGlobalFields($query)
    {
        return $query->where('preset_type', static::PRESET_TYPE['global']);
    }

    /**
     * 限定仅查询全局预设字段
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearchableFields($query)
    {
        return $query->where('is_searchable', true);
    }

    /**
     * 获取所有字段的信息（包含参数）
     *
     * @return \Illuminate\Support\Collection
     */
    public static function retrieveFieldsInfo()
    {
        return static::query()->with('fieldParameters')->get()
            ->map(function(NodeField $field) {
                return $field->gather();
            });

        // $pocket = new Pocket(static::class);
        // $key = 'field_infos';
        // $event = static::class.'/'.$key.':created';

        // if ($data = $pocket->get($key)) {
        //     $events = [
        //         static::class.':changed',
        //         FieldParameters::class.':changed',
        //     ];
        //     if (! events()->hasHappenedAfter($events, $event)) {
        //         return collect($data->value());
        //     }
        // }

        // $data = static::query()->with('fieldParameters')->get()
        //     ->map(function(NodeField $field) {
        //         return $field->gather();
        //     })->all();

        // $pocket->put($key, $data);

        // events()->record($event);

        // return collect($data);
    }

    /**
     * 获取全局字段的信息
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takeGlobalFieldsInfo()
    {
        return static::globalFields()->with('fieldParameters')->get()
            ->map(function(NodeField $field) {
                return $field->gather();
            });

        // return static::retrieveFieldsInfo()->groupBy('preset_type')->get(static::PRESET_TYPE['global']);
    }

    /**
     * 获取预设字段的信息
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takePresetFieldsInfo()
    {
        return static::presetFields()->with('fieldParameters')->get()
            ->map(function(NodeField $field) {
                return $field->gather();
            });

        // return static::retrieveFieldsInfo()->groupBy('preset_type')->get(static::PRESET_TYPE['preset']);
    }

    /**
     * 获取常规字段的信息
     *
     * @return \Illuminate\Support\Collection
     */
    public static function takeSelectableFieldsInfo()
    {
        return static::normalFields()->with('fieldParameters')->get()
            ->map(function(NodeField $field) {
                return $field->gather();
            });

        // return static::retrieveFieldsInfo()->groupBy('preset_type')->get(static::PRESET_TYPE['normal']);
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
