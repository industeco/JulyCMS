<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Node;
use App\ModelIsomers\CatalogTree;
use App\Contracts\GetNodes;
use App\ModelCollections\NodeCollection;
use App\Traits\TruenameAsPrimaryKey;

class Catalog extends JulyModel implements GetNodes
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

    public function nodes()
    {
        return $this->belongsToMany(Node::class, 'catalog_node', 'catalog', 'node_id')
                ->withPivot([
                    'parent_id',
                    'prev_id',
                    'path',
                    'langcode',
                ]);
    }

    public function nodesMerged()
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            $values = $node->gather();
            $values['parent_id'] = $node->pivot->parent_id;
            $values['prev_id'] = $node->pivot->prev_id;
            $values['path'] = $node->pivot->path;
            $nodes[] = $values;
        }
        return $nodes;
    }

    public static function allPositions()
    {
        $positions = CatalogNode::all()->groupBy('catalog')->toArray();
        foreach (Catalog::all() as $catalog) {
            if (! isset($positions[$catalog->truename])) {
                $positions[$catalog->truename] = [];
            }
        }
        return $positions;
    }

    public function positions()
    {
        return CatalogNode::where('catalog', $this->truename)->get()->toArray();
    }

    public function removePosition(array $position)
    {
        $this->cacheClear(['name'=>'catalogNodes']);
        $this->cacheClear(['name'=>'treeNodes']);

        // DB::delete("DELETE from catalog_node where `catalog`=? and (`node_id`=? or `path` like '%/$node_id/%' )");
        CatalogNode::where([
            'catalog' => $this->truename,
            'node_id' => $position['node_id'],
        ])->orWhere([
            ['catalog', '=', $this->truename],
            ['path', 'like', '%/'.$position['node_id'].'/%'],
        ])->delete();

        $this->touch();
    }

    public function insertPosition(array $position)
    {
        $this->cacheClear(['name'=>'catalogNodes']);
        $this->cacheClear(['name'=>'treeNodes']);

        // $position['catalog'] = $this->truename;
        $position['langcode'] = langcode('content');

        $parent = $position['parent_id'];
        if ($parent) {
            $parent = CatalogNode::where([
                'catalog' => $position['catalog'],
                'node_id' => $parent,
            ])->firstOrFail();
            $position['path'] = $parent->path.$position['parent_id'].'/';
        } else {
            $position['path'] = '/';
        }

        $next = CatalogNode::where([
            'catalog' => $position['catalog'],
            'parent_id' => $position['parent_id'],
            'prev_id' => $position['prev_id'],
        ])->first();

        if ($next) {
            $next->prev_id = $position['node_id'];
            $next->save();
        }

        CatalogNode::create($position);

        $this->touch();
    }

    public function updatePositions(array $positions)
    {
        $this->cacheClear(['name'=>'catalogNodes']);
        $this->cacheClear(['name'=>'treeNodes']);

        $supplement = [
            'catalog' => $this->truename,
            'langcode' => langcode('content'),
        ];
        foreach ($positions as &$position) {
            $position = array_merge($position, $supplement);
        }
        unset($position);

        DB::table('catalog_node')->where('catalog', $this->truename)->delete();
        DB::transaction(function() use ($positions) {
            foreach ($positions as $position) {
                DB::table('catalog_node')->insert($position);
            }
        });

        $this->touch();
    }

    public function retrieveCatalogNodes()
    {
        $cachekey = $this->cacheKey('catalogNodes', []);
        if ($nodes = $this->cacheGet($cachekey)) {
            $nodes = $nodes['value'];
        } else {
            $nodes = CatalogNode::where('catalog', $this->truename)
                ->get(['node_id','parent_id','prev_id','path'])->toArray();

            $this->cachePut($cachekey, $nodes);
        }

        return $nodes;
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
     * @return \App\ModelCollections\NodeCollection
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
        return NodeCollection::find($ids);
    }

    public function get_under(...$args)
    {
        return $this->get_children(...$args);
    }

    /**
     * 获取指定节点的所有子节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\NodeCollection
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
        return NodeCollection::find($ids);
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
     * @return \App\ModelCollections\NodeCollection
     */
    public function get_parent(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new NodeCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->parent($id));
        }
        return NodeCollection::find($ids);
    }

    public function get_over(...$args)
    {
        return $this->get_parent(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 指定节点
     * @return \App\ModelCollections\NodeCollection
     */
    public function get_ancestors(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new NodeCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->ancestors($id));
        }
        return NodeCollection::find($ids);
    }

    public function get_above(...$args)
    {
        return $this->get_ancestors(...$args);
    }

    /**
     * 获取指定节点的相邻节点
     *
     * @param array $args 指定节点
     * @return NodeCollection
     */
    public function get_siblings(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return new NodeCollection;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->siblings($id));
        }
        return NodeCollection::find($ids);
    }

    public function get_around(...$args)
    {
        return $this->get_siblings(...$args);
    }

    /**
     * 在指定的树中，获取当前节点的前一个节点
     *
     * @param int $id
     * @return \App\Models\Node
     */
    public function get_prev($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->prev($id)) {
            return Node::fetch($id);
        }
        return null;
    }

    /**
     * 在指定的树中，获取当前节点的后一个节点
     *
     * @param int $id
     * @return \App\Models\Node
     */
    public function get_next($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->next($id)) {
            return Node::fetch($id);
        }
        return null;
    }

    public function get_nodes(): NodeCollection
    {
        $ids = $this->tree()->nodes();
        return NodeCollection::find($ids);
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
