<?php

namespace App\Entity;

use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;
use App\Models\ModelBase;
use App\Utils\Arr;

abstract class EntityMoldBase extends ModelBase implements TranslatableInterface
{
    use TranslatableTrait;

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
     * 获取实体类
     *
     * @return string
     */
    abstract public static function getEntityClass();

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldClass()
    {
        return static::getEntityClass()::getFieldClass();
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotClass()
    {
        return static::getEntityClass()::getPivotClass();
    }

    /**
     * 获取模型模板数据
     *
     * @return array
     */
    public static function template()
    {
        return [
            'id' => null,
            'label' => null,
            'description' => null,
            'langcode' => langcode('content'),
        ];
    }

    /**
     * 获取模型列表数据
     *
     * @return array[]
     */
    public static function index()
    {
        // 统计每个类型被节点引用次数（也就是有多少个节点使用该类型）
        $referenced = static::referencedByEntity();

        // 获取模型列表
        $molds = parent::index();

        // 补充引用计数
        foreach ($molds as $key => &$mold) {
            $mold['referenced'] = $referenced[$key] ?? 0;
        }

        return $molds;
    }

    /**
     * 引用计数
     *
     * @return array
     */
    public static function referencedByEntity()
    {
        return static::getEntityClass()::query()->selectRaw('`mold_id`, COUNT(*) as `total`')
            ->groupBy('mold_id')
            ->pluck('total', 'mold_id')->all();
    }

    /**
     * 当前类型下的所有节点
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entities()
    {
        return $this->hasMany(static::getEntityClass(), 'mold_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        $pivotModel = static::getPivotClass();
        $pivot = (new $pivotModel)->getTable();
        return $this->belongsToMany(static::getFieldClass(), $pivot, 'mold_id', 'field_id')
            ->orderBy($pivot.'.delta')
            ->withPivot([
                'delta',
                'label',
                'description',
                'is_required',
                'helpertext',
                'default_value',
                'options',
            ]);
    }

    /**
     * 同步关联字段
     *
     * @param  array|null $fields
     * @return array
     */
    public function syncFields(array $fields = null)
    {
        $fields = $fields ?? $this->raw['fields'] ?? [];
        $keys = ['delta','label','description','is_required','helpertext','default_value','options'];
        $relatedFields = [];
        foreach (array_values($fields) as $index => $field) {
            $field['delta'] = $index;
            $relatedFields[$field['id']] = Arr::only($field, $keys);
        }
        $this->fields()->sync($relatedFields);
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        // 创建或更新后同步关联字段
        // 字段数据保存在 $raw 属性中
        static::saved(function(EntityMoldBase $mold) {
            $mold->syncFields();
        });

        // 删除时移除关联字段
        static::deleting(function(EntityMoldBase $mold) {
            $mold->fields()->detach();
        });
    }
}
