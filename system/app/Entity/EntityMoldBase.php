<?php

namespace App\Entity;

use App\Services\Translation\TranslatableInterface;
use App\Services\Translation\TranslatableTrait;
use App\Models\ModelBase;

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
    abstract public static function getEntityModel();

    /**
     * 获取实体字段类
     *
     * @return string
     */
    public static function getFieldModel()
    {
        return static::getEntityModel()::getFieldModel();
    }

    /**
     * 获取类型字段关联类
     *
     * @return string
     */
    public static function getPivotModel()
    {
        return static::getEntityModel()::getPivotModel();
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
        return static::getEntityModel()::query()->selectRaw('`mold_id`, COUNT(*) as `total`')
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
        return $this->hasMany($this->getEntityModel(), 'mold_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        $pivot = (new $this->getPivotModel())->getTable();
        return $this->belongsToMany($this->getFieldModel(), $pivot, 'mold_id', 'field_id')
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
}
