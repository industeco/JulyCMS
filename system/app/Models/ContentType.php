<?php

namespace App\Models;

use App\Contracts\GetContents;
use App\FieldTypes\FieldType;
use App\ModelCollections\ContentCollection;
use App\Traits\TruenameAsPrimaryKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContentType extends JulyModel implements GetContents
{
    use TruenameAsPrimaryKey;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'content_types';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'is_preset',
        'label',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
    ];

    public function contents()
    {
        return $this->hasMany(Content::class, 'content_type');
    }

    public function fields()
    {
        return $this->belongsToMany(ContentField::class, 'content_field_content_type', 'content_type', 'content_field')
            ->orderBy('content_field_content_type.delta')
            ->withPivot([
                'delta',
                'weight',
                'label',
                'description',
            ]);
    }

    public static function usedByContents()
    {
        $contenttypeUsed = [];
        $records = DB::select('SELECT `content_type`, count(`content_type`) as `total` FROM `contents` GROUP BY `content_type`');
        foreach ($records as $record) {
            $contenttypeUsed[$record->content_type] = $record->total;
        }

        return $contenttypeUsed;
    }

    /**
     * 获取字段数据
     *
     * @param string|null $langcode
     * @return array
     */
    public function cacheGetFields($langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $cacheKey = $this->cacheKey(['key'=>'fields', 'langcode'=>$langcode]);

        if ($fields = $this->cacheGet($cacheKey)) {
            return $fields['value'];
        }

        $fields = $this->fields->map(function($field) use($langcode) {
            return $field->gather($langcode);
        })->keyBy('truename')->all();
        $this->cachePut($cacheKey, $fields);

        return $fields;
    }

    /**
     * 获取字段拼图（与字段相关的一组信息，用于组成表单）
     *
     * @param string|null $langcode
     * @param array $values
     * @return array
     */
    public function cacheGetFieldJigsaws($langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $cacheKey = $this->cacheKey(['key'=>'fieldJigsaws', 'langcode'=>$langcode]);

        if ($jigsaws = $this->cacheGet($cacheKey)) {
            $jigsaws = $jigsaws['value'];
        }

        $lastModified = last_modified(background_path('template/components/'));
        if (!$jigsaws || $jigsaws['created_at'] < $lastModified) {
            $jigsaws = [];
            foreach ($this->cacheGetFields($langcode) as $field) {
                $jigsaws[$field['truename']] = FieldType::getJigsaws($field);
            }
            $jigsaws = [
                'created_at' => time(),
                'jigsaws' => $jigsaws,
            ];
            $this->cachePut($cacheKey, $jigsaws);
        }

        return $jigsaws['jigsaws'];
    }

    /**
     * 更新类型字段
     *
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function updateFields(array $newFields)
    {
        // Log::info($fields);
        $langcode = langcode('content');

        // 清除碎片缓存
        $this->cacheClear(['key'=>'fields', 'langcode'=>$langcode]);
        $this->cacheClear(['key'=>'fieldJigsaws', 'langcode'=>$langcode]);

        DB::beginTransaction();

        $fields = [];
        foreach ($newFields as $index => $field) {
            $fields[$field['truename']] = [
                'delta' => $index,
                'weight' => $field['weight'] ?? 1,
                'label' => $field['label'] ?? null,
                'description' => $field['description'] ?? null,
            ];

            FieldParameters::updateOrCreate([
                'keyname' => implode('.', ['content_field', $field['truename'], 'content_type', $this->getKey(), $langcode]),
            ], ['data' => FieldType::extractParameters($field)]);
        }
        $this->fields()->sync($fields);

        DB::commit();
    }

    public function get_contents(): ContentCollection
    {
        return ContentCollection::make($this->contents->keyBy('id')->all());
    }
}
