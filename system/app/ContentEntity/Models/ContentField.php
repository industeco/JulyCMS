<?php

namespace App\ContentEntity\Models;

use App\Entity\Models\BaseEntityField;
use App\EntityFieldTypes\EntityFieldType;
use App\Traits\TruenameAsPrimaryKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContentField extends BaseEntityField
{
    use TruenameAsPrimaryKey;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'content_fields';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'field_type',
        'is_preset',
        'is_global',
        'is_searchable',
        'weight',
        'group',
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
        'is_preset' => 'boolean',
        'is_global' => 'boolean',
        'is_searchable' => 'boolean',
        'weight' => 'float',
    ];

    public static function getEntityId()
    {
        return 'content_field';
    }

    public static function getParentEntityId()
    {
        return 'content';
    }

    public function types()
    {
        return $this->belongsToMany(ContentType::class, 'content_field_content_type', 'content_field', 'content_type')
                    ->orderBy('content_field_content_type.delta')
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
    public static function globalFields()
    {
        return static::where('is_global', true)->get();
    }

    /**
     * 获取所有非全局字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function localFields()
    {
        return static::where('is_global', false)->get();
    }

    /**
     * 获取所有预设字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function presetFields()
    {
        return static::where('is_preset', true)->get();
    }

    /**
     * 获取所有非全局预设字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function presetLocalFields()
    {
        return static::where('is_preset', true)->where('is_global', false)->get();
    }

    /**
     * 获取所有普通（非预设）字段
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function commonFields()
    {
        return static::where('is_preset', false)->get();
    }

    /**
     * 从缓存获取所有全局字段
     *
     * @return array
     */
    public static function cacheGetGlobalFields($langcode = null)
    {
        $langcode = $langcode ?? langcode('content');

        $model = new static;
        $cacheKey = $model->cacheKey(['key'=>'globalFields', 'langcode'=>$langcode]);

        if ($fields = $model->cacheGet($cacheKey)) {
            return $fields['value'];
        }

        $fields = static::globalFields()->map(function($field) use($langcode) {
            return $field->gather($langcode);
        })->keyBy('truename')->all();

        $model->cachePut($cacheKey, $fields);

        return $fields;
    }

    public static function cacheGetGlobalFieldJigsaws($langcode = null)
    {
        $langcode = $langcode ?? langcode('content');

        $model = new static;
        $cacheKey = $model->cacheKey(['key'=>'globalFieldJigsaws', 'langcode'=>$langcode]);

        if ($jigsaws = $model->cacheGet($cacheKey)) {
            $jigsaws = $jigsaws['value'];
        }

        $lastModified = last_modified(backend_path('template/components/'));
        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach (static::cacheGetGlobalFields($langcode) as $field) {
                $jigsaws[$field['truename']] = EntityFieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            $model->cachePut($cacheKey, $jigsaws);
        }

        return $jigsaws['jigsaws'];
    }
}
