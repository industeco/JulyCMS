<?php

namespace App\Entity;

use App\EntityField\FieldBase;
use App\Support\Translation\TranslatableInterface;
use App\Support\Translation\TranslatableTrait;
use App\Models\ModelBase;
use App\Support\Arr;
use Illuminate\Support\Facades\Log;

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
     * @param  array $columns 选取的列
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function index(array $keys = ['*'])
    {
        // 统计每个类型被节点引用次数（也就是有多少个节点使用该类型）
        $referenced = static::referencedByEntity();

        // 获取模型列表
        $molds = parent::index($keys)->all();

        // 补充引用计数
        foreach ($molds as $key => &$mold) {
            $mold['referenced'] = $referenced[$key] ?? 0;
        }

        return collect($molds);
    }

    /**
     * 引用计数
     *
     * @return array
     */
    public static function referencedByEntity()
    {
        $moldKey = static::getEntityClass()::getMoldKeyName();
        return static::getEntityClass()::query()->selectRaw('`'.$moldKey.'`, COUNT(*) as `total`')
            ->groupBy($moldKey)
            ->pluck('total', $moldKey)->all();
    }

    /**
     * 当前类型下的所有节点
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function entities()
    {
        $class = static::getEntityClass();
        return $this->hasMany($class, $class::getMoldKeyName());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function fields()
    {
        $pivot = static::getPivotClass();
        $pivotTable = $pivot::getModelTable();
        return $this->belongsToMany(static::getFieldClass(), $pivotTable, $pivot::getMoldKeyName(), $pivot::getFieldKeyName())
            ->withPivot([
                'label',
                'description',
                'delta',
                'field_meta',
            ])
            ->orderBy($pivotTable.'.delta');
    }

    /**
     * 获取类型关联字段
     *
     * @return \Illuminate\Support\Collection|\App\EntityField\FieldBase[]
     */
    public function getFields()
    {
        if ($fields = $this->cachePipe(__FUNCTION__)) {
            return $fields->value();
        }
        return $this->fields;
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

        $keys = static::getPivotClass()::getMetaAttributes();
        $relatedFields = [];
        foreach (array_values($fields) as $index => $field) {
            $meta = (new $field['field_type'])->extractMeta($field);
            $field['field_meta'] = serialize($meta);
            $field['delta'] = $index;
            $relatedFields[$field['id']] = Arr::only($field, $keys);
        }
        $this->fields()->sync($relatedFields);
    }

    /**
     * 获取字段默认值
     *
     * @return array
     */
    public function getFieldValues()
    {
        return $this->getFields()->map(function(FieldBase $field) {
            return [
                'id' => $field->getKey(),
                'value' => $field->getDefaultValue(),
            ];
        })->pluck('value', 'id')->all();
    }

    /**
     * 获取字段属性及渲染后的表单控件
     *
     * @return array
     */
    public function fieldsToArray()
    {
        if ($results = $this->cachePipe(__FUNCTION__)) {
            return $results->value();
        }

        return $this->getFields()->map(function(FieldBase $field) {
            return $field->gather();
        })->all();
    }

    /**
     * 获取字段属性及渲染后的表单控件
     *
     * @return array
     */
    public function fieldsToControls()
    {
        if ($results = $this->pocketPipe(__FUNCTION__)) {
            return $results->value();
        }

        return $this->getFields()->map(function(FieldBase $field) {
            return $field->gather() + [
                'control' => $field->render(),
            ];
        })->all();
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
            if ($mold->getRaw()) {
                $mold->syncFields();
                $mold->clearRaw();
            }
            $mold->pocket()->clear('fieldsToControls');
        });

        // 删除时移除关联字段
        static::deleting(function(EntityMoldBase $mold) {
            $mold->fields()->detach();
            $mold->pocket()->clear('fieldsToControls');
        });
    }
}
