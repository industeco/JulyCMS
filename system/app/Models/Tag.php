<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Tag extends JulyModel
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'tags';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'tag';

    /**
     * 主键“类型”。
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * 指示模型主键是否递增
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
        'tag',
        'is_preset',
        'is_show',
        'original',
        'langcode',
        'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'is_show' => 'boolean',
    ];

    public function nodes($langcode = null)
    {
        if ($langcode) {
            return $this->belongsToMany(Node::class, 'node_tag', 'tag', 'node_id')
                ->wherePivot('langcode', $langcode);
        }
        return $this->belongsToMany(Node::class, 'node_tag', 'tag', 'node_id')
            ->withPivot('langcode');
    }

    public static function tags($langcode = null)
    {
        if ($langcode) {
            return static::where('langcode', $langcode)->get()->pluck('tag')->toArray();
        }
        return static::all()->pluck('tag')->toArray();
    }

    public static function createIfNotExist(array $tags, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content_value');

        $records = [];
        $freshTags = collect($tags)->diff(Tag::tags());
        foreach ($freshTags as $tag) {
            $records[] = [
                'tag' => $tag,
                'is_preset' => false,
                'is_show' => true,
                'original' => $tag,
                'langcode' => $langcode,
            ];
        }
        if ($records) {
            DB::table('tags')->insert($records);
        }

        return count($records);
    }
}
