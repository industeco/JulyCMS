<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Content;
use App\ModelIsomers\CatalogTree;
use App\Contracts\GetContents;
use App\ModelCollections\ContentCollection;
use App\Traits\TruenameAsPrimaryKey;

class Catalog extends JulyModel implements GetContents
{
    use TruenameAsPrimaryKey;

    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'catalogs';

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

    /**
     * 排序后的目录内容
     *
     * @var \App\ModelIsomers\CatalogTree
     */
    protected $catalogTree = null;

    public static function default()
    {
        return static::fetch('main');
    }

    public function contents()
    {
        return $this->belongsToMany(Content::class, 'catalog_content', 'catalog', 'content_id')
                ->withPivot([
                    'parent_id',
                    'prev_id',
                    'path',
                ]);
    }

    public function contentsMerged()
    {
        $contents = [];
        foreach ($this->contents as $content) {
            $values = $content->gather();
            $values['parent_id'] = $content->pivot->parent_id;
            $values['prev_id'] = $content->pivot->prev_id;
            $values['path'] = $content->pivot->path;
            $contents[] = $values;
        }
        return $contents;
    }

    public static function allPositions()
    {
        $positions = CatalogContent::all()->groupBy('catalog')->toArray();
        foreach (Catalog::all() as $catalog) {
            $truename = $catalog->getKey();
            if (! isset($positions[$truename])) {
                $positions[$truename] = [];
            }
        }
        return $positions;
    }

    public function positions()
    {
        return CatalogContent::where('catalog', $this->getKey())->get()->toArray();
    }

    public function cacheGetCatalogContents()
    {
        $cachekey = $this->cacheKey('catalogNodes', []);
        if ($contents = $this->cacheGet($cachekey)) {
            $contents = $contents['value'];
        } else {
            $contents = CatalogContent::where('catalog', $this->getKey())
                ->get(['content_id','parent_id','prev_id','path'])->toArray();

            $this->cachePut($cachekey, $contents);
        }

        return $contents;
    }

    public function removePosition(array $position)
    {
        $this->cacheClear(['key'=>'catalogNodes']);
        $this->cacheClear(['key'=>'treeNodes']);

        // DB::delete("DELETE from catalog_content where `catalog`=? and (`content_id`=? or `path` like '%/$content_id/%' )");
        $truename = $this->getKey();
        CatalogContent::where([
            'catalog' => $truename,
            'content_id' => $position['content_id'],
        ])->orWhere([
            ['catalog', '=', $truename],
            ['path', 'like', '%/'.$position['content_id'].'/%'],
        ])->delete();

        $this->touch();
    }

    public function insertPosition(array $position)
    {
        $this->cacheClear(['key'=>'catalogNodes']);
        $this->cacheClear(['key'=>'treeNodes']);

        // $position['catalog'] = $this->truename;
        $position['langcode'] = langcode('content');

        $parent = $position['parent_id'];
        if ($parent) {
            $parent = CatalogContent::where([
                'catalog' => $position['catalog'],
                'content_id' => $parent,
            ])->firstOrFail();
            $position['path'] = $parent->path.$position['parent_id'].'/';
        } else {
            $position['path'] = '/';
        }

        $next = CatalogContent::where([
            'catalog' => $position['catalog'],
            'parent_id' => $position['parent_id'],
            'prev_id' => $position['prev_id'],
        ])->first();

        if ($next) {
            $next->prev_id = $position['content_id'];
            $next->save();
        }

        CatalogContent::create($position);

        $this->touch();
    }

    public function updatePositions(array $positions)
    {
        $this->cacheClear(['key'=>'catalogNodes']);
        $this->cacheClear(['key'=>'treeNodes']);

        $truename = $this->getKey();
        // $supplement = [
        //     'catalog' => $truename,
        //     'langcode' => langcode('content'),
        // ];
        // foreach ($positions as &$position) {
        //    $position = array_merge($position, $supplement);
        // }
        // unset($position);

        DB::beginTransaction();

        DB::table('catalog_content')->where('catalog', $truename)->delete();
        foreach ($positions as $position) {
            $position['catalog'] = $truename;
            DB::table('catalog_content')->insert($position);
        }

        DB::commit();

        $this->touch();
    }

    /**
     * @return \App\ModelIsomers\CatalogTree
     */
    public function tree()
    {
        if (! $this->catalogTree) {
            $this->catalogTree = new CatalogTree($this);
        }
        return $this->catalogTree;
    }


    /**
     * 获取指定节点的直接子节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\ContentCollection
     */
    public function get_children(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->children($id));
        }
        return ContentCollection::find($ids);
    }

    public function get_under(...$args)
    {
        return $this->get_children(...$args);
    }

    /**
     * 获取指定节点的所有子节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\ContentCollection
     */
    public function get_descendants(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->descendants($id));
        }
        return ContentCollection::find($ids);
    }

    /**
     * get_descendants 的别名
     */
    public function get_below(...$args)
    {
        return $this->get_descendants(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\ContentCollection
     */
    public function get_parent(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new ContentCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->parent($id));
        }
        return ContentCollection::find($ids);
    }

    public function get_over(...$args)
    {
        return $this->get_parent(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\ContentCollection
     */
    public function get_ancestors(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new ContentCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->ancestors($id));
        }
        return ContentCollection::find($ids);
    }

    public function get_above(...$args)
    {
        return $this->get_ancestors(...$args);
    }

    /**
     * 获取指定节点的相邻节点
     *
     * @param array $args 指定节点
     * @return ContentCollection
     */
    public function get_siblings(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new ContentCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->siblings($id));
        }
        return ContentCollection::find($ids);
    }

    public function get_around(...$args)
    {
        return $this->get_siblings(...$args);
    }

    /**
     * 在指定的树中，获取当前节点的前一个节点
     *
     * @param int $id
     * @return \App\Models\Content
     */
    public function get_prev($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->prev($id)) {
            return Content::fetch($id);
        }
        return null;
    }

    /**
     * 在指定的树中，获取当前节点的后一个节点
     *
     * @param int $id
     * @return \App\Models\Content
     */
    public function get_next($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->next($id)) {
            return Content::fetch($id);
        }
        return null;
    }

    public function get_contents(): ContentCollection
    {
        $ids = $this->tree()->contents();
        return ContentCollection::find($ids);
    }

    /**
     * 获取指定节点的路径（节点 id 集合）
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function get_path($id)
    {
        if (!$id) {
            return collect();
        }

        return collect($this->tree()->ancestors($id));
    }
}
