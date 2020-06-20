<?php

namespace App\Models;

use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use App\Contracts\GetNodes;
use App\ModelCollections\NodeCollection;

class Tag extends JulyModel implements GetNodes
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
        'original_tag',
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

    public static function createIfNotExist(array $tags, $langcode = null)
    {
        $langcode = $langcode ?: langcode('content');
        $currentTags = Tag::all()->keyBy('tag');
        $count = 0;

        DB::beginTransaction();

        foreach ($tags as $tag) {
            if (! $currentTags->has($tag)) {
                static::create([
                    'tag' => $tag,
                    'original_tag' => $tag,
                    'langcode' => $langcode,
                ]);
                $count++;
            }
        }

        DB::commit();

        return $count;
    }

    public function nodes($langcode = null)
    {
        if ($langcode) {
            return $this->belongsToMany(Node::class, 'node_tag', 'tag', 'node_id')
                ->wherePivot('langcode', $langcode);
        }
        return $this->belongsToMany(Node::class, 'node_tag', 'tag', 'node_id')
            ->withPivot('langcode');
    }

    public static function allTags($langcode = null)
    {
        if ($langcode) {
            return static::where('langcode', $langcode)->get()->pluck('tag')->all();
        }
        return static::all()->pluck('tag')->all();
    }

    public function getRightTag($langcode = null)
    {
        if ($this->langcode === $langcode) {
            return $this->attributes['tag'];
        }

        $cluster = static::retrieveTagCluster($this->attributes['original_tag']);
        if ($langcode) {
            return $cluster[$langcode] ?? $this->attributes['tag'];
        }

        return $cluster;
    }

    public static function retrieveTagCluster($original_tag)
    {
        if ($cluster = static::cacheGet($original_tag)) {
            return $cluster['value'];
        }

        $cluster = Tag::where('original_tag', $original_tag)->get()
                    ->pluck('tag', 'langcode')->toArray();
        static::cachePut($original_tag, $cluster);

        return $cluster;
    }

    public static function saveChange(array $changes)
    {
        $tags = Tag::findMany(array_keys($changes))->keyBy('tag')->all();

        $prepareDelete = [];
        $prepareCreate = [];

        foreach ($changes as $key => $value) {
            $tag = $tags[$key] ?? null;
            if ($tag) {
                if ($value) {
                    $tag->is_show = $value['is_show'];
                    $tag->original_tag = $value['original_tag'];
                    $tag->save();
                } else {
                    $prepareDelete[] = $key;
                }
            } elseif ($value) {
                $time = Date::createFromTimestampMs($value['updated_at']);
                $prepareCreate[] = array_replace($value, [
                    'created_at' => $time,
                    'updated_at' => $time,
                ]);
            }
        }

        if ($prepareDelete) {
            DB::table('tags')->whereIn('tag', $prepareDelete)->delete();
            DB::table('node_tag')->whereIn('tag', $prepareDelete)->delete();
        }

        if ($prepareCreate) {
            DB::table('tags')->insert($prepareCreate);
        }
    }

    public function get_nodes(): NodeCollection
    {
        $ids = $this->nodes()->pluck('id')->unique()->all();
        return NodeCollection::find($ids);
    }
}
