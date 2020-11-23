<?php

namespace July\Core\Node;

use App\Utils\Arr;
use App\Utils\Pocket;
use Illuminate\Support\Facades\DB;
use July\Core\Entity\EntityBase;

class Catalog extends EntityBase implements GetNodesInterface
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'catalogs';

    /**
     * 主键
     *
     * @var string
     */
    protected $primaryKey = 'id';

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
        'id',
        'is_necessary',
        'label',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_necessary' => 'boolean',
    ];

    /**
     * 内建属性登记处
     *
     * @var array
     */
    protected static $columns = [
        'id',
        'is_necessary',
        'label',
        'description',
        'created_at',
        'updated_at',
    ];

    /**
     * 排序后的目录内容
     *
     * @var \July\Core\Node\CatalogTree
     */
    protected $catalogTree = null;

    public static function default()
    {
        return static::findOrFail('main');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function nodes()
    {
        return $this->belongsToMany(Node::class)
                ->withPivot([
                    'parent_id',
                    'prev_id',
                    'path',
                ]);
    }

    public function nodesMerged()
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            $values = $node->entityToArray();
            $values['parent_id'] = $node->pivot->parent_id;
            $values['prev_id'] = $node->pivot->prev_id;
            $values['path'] = $node->pivot->path;
            $nodes[] = $values;
        }

        return $nodes;
    }

    public static function allPositions()
    {
        $positions = CatalogNode::all()->groupBy('catalog_id')->toArray();
        foreach (Catalog::query()->pluck('id') as $catalog_id) {
            if (! isset($positions[$catalog_id])) {
                $positions[$catalog_id] = [];
            }
        }

        return $positions;
    }

    public function positions()
    {
        return CatalogNode::query()->where('catalog_id', $this->getKey())->get()->toArray();
    }

    public function retrieveNodePositions()
    {
        return CatalogNode::query()->where('catalog_id', $this->getKey())
                ->get(['node_id','parent_id','prev_id','path'])->toArray();

        // $pocket = new Pocket($this);
        // $key = $pocket->key('nodes');

        // if ($nodes = $pocket->get($key)) {
        //     $nodes = $nodes->value;
        // } else {
        //     $nodes = CatalogNode::query()->where('catalog_id', $this->getKey())
        //         ->get(['id','parent_id','prev_id','path'])->toArray();

        //     $pocket->put($key, $nodes);
        // }

        // return $nodes;
    }

    public function removePosition(array $position)
    {
        $pocket = new Pocket($this);
        // $pocket->clear('nodes');
        $pocket->clear('treeNodes');

        // DB::delete("DELETE from catalog_content where `catalog`=? and (`content_id`=? or `path` like '%/$content_id/%' )");
        $id = $this->getKey();
        CatalogNode::where([
            'catalog' => $id,
            'node_id' => $position['id'],
        ])->orWhere([
            ['catalog', '=', $id],
            ['path', 'like', '%/'.$position['id'].'/%'],
        ])->delete();

        $this->touch();
    }

    public function insertPosition(array $position)
    {
        $pocket = new Pocket($this);
        // $pocket->clear('nodes');
        $pocket->clear('treeNodes');

        // $position['catalog'] = $this->id;
        $position['langcode'] = langcode('content');

        $parent = $position['parent_id'];
        if ($parent) {
            $parent = CatalogNode::where([
                'catalog' => $position['catalog'],
                'content_id' => $parent,
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
            $next->prev_id = $position['id'];
            $next->save();
        }

        CatalogNode::create($position);

        $this->touch();
    }

    public function updatePositions(array $positions)
    {
        $pocket = new Pocket($this);
        // $pocket->clear('nodes');
        $pocket->clear('treeNodes');

        $id = $this->getKey();

        DB::beginTransaction();

        DB::table('catalog_node')->where('catalog_id', $id)->delete();
        foreach ($positions as $position) {
            $position['catalog_id'] = $id;
            $position = Arr::only($position, ['catalog_id','node_id','parent_id','prev_id','path']);
            DB::table('catalog_node')->insert($position);
        }

        DB::commit();

        $this->touch();
    }

    /**
     * @return \July\Core\Node\CatalogTree
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
     * @return \July\Core\Node\NodeSet
     */
    public function get_children(...$args)
    {
        $args = normalize_args($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->children($id));
        }

        return NodeSet::find($ids);
    }

    public function get_under(...$args)
    {
        return $this->get_children(...$args);
    }

    /**
     * 获取指定节点的所有子节点
     *
     * @param array $args 指定节点
     * @return \July\Core\Node\NodeSet
     */
    public function get_descendants(...$args)
    {
        $args = normalize_args($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->descendants($id));
        }

        return NodeSet::find($ids);
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
     * @return \July\Core\Node\NodeSet
     */
    public function get_parent(...$args)
    {
        $args = normalize_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->parent($id));
        }

        return NodeSet::find($ids);
    }

    public function get_over(...$args)
    {
        return $this->get_parent(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 指定节点
     * @return \July\Core\Node\NodeSet
     */
    public function get_ancestors(...$args)
    {
        $args = normalize_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->ancestors($id));
        }

        return NodeSet::find($ids);
    }

    public function get_above(...$args)
    {
        return $this->get_ancestors(...$args);
    }

    /**
     * 获取指定节点的相邻节点
     *
     * @param array $args 指定节点
     * @return \July\Core\Node\NodeSet
     */
    public function get_siblings(...$args)
    {
        $args = normalize_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->siblings($id));
        }

        return NodeSet::find($ids);
    }

    public function get_around(...$args)
    {
        return $this->get_siblings(...$args);
    }

    /**
     * 在指定的树中，获取当前节点的前一个节点
     *
     * @param int $id
     * @return \July\Core\Node\Node|null
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
     * @return \July\Core\Node\Node|null
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

    public function get_nodes()
    {
        $ids = $this->tree()->nodes();
        return NodeSet::find($ids);
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
