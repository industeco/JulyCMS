<?php

namespace July\Node;

use Illuminate\Support\Facades\DB;
use App\Models\ModelBase;
use App\Support\Tree;

class Catalog extends ModelBase implements GetNodesInterface
{
    /**
     * 缓存的默认目录
     *
     * @var \July\Node\Catalog|null
     */
    protected static $defaultCatalog = null;

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
        'label',
        'description',
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
     * 获取对应的模型集类
     *
     * @return string|null
     */
    public static function getModelSetClass()
    {
        return CatalogSet::class;
    }

    /**
     * 树状结构的目录数据
     *
     * @var \App\Support\Tree
     */
    protected $tree = null;

    /**
     * {@inheritdoc}
     */
    public static function template()
    {
        return [
            'id' => null,
            'label' => null,
            'description' => null,
            'is_reserved' => false,
        ];
    }

    /**
     * 获取默认目录
     *
     * @return \July\Node\Catalog|static
     */
    public static function default()
    {
        if (app()->has('catalog.default')) {
            return app('catalog.default');
        }
        app()->instance('catalog.default', $catalog = static::findOrFail('main'));
        return $catalog;
    }

    /**
     * 节点关联
     *
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

    /**
     * 获取目录内位置数据
     *
     * @return array
     */
    public function getPositions()
    {
        $positions = [];
        foreach (CatalogNode::ofCatalog($this)->get() as $position) {
            $positions[] = [
                'id' => $position->node_id,
                'parent_id' => $position->parent_id ?? null,
                'prev_id' => $position->prev_id,
            ];
        }
        return $positions;
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        static::deleting(function(Catalog $catalog) {
            $catalog->nodes()->detach();
        });
    }

    // public function nodesMerged()
    // {
    //     $nodes = [];
    //     foreach ($this->nodes as $node) {
    //         $values = $node->entityToArray();
    //         $values['parent_id'] = $node->pivot->parent_id;
    //         $values['prev_id'] = $node->pivot->prev_id;
    //         $values['path'] = $node->pivot->path;
    //         $nodes[] = $values;
    //     }

    //     return $nodes;
    // }

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

    // public function retrieveNodePositions()
    // {
    //     return CatalogNode::query()->where('catalog_id', $this->getKey())
    //             ->get(['node_id','parent_id','prev_id','path'])->toArray();
    // }

    public function updatePositions(array $positions)
    {
        $positions = (new Tree($positions))->getNodes();
        $id = $this->getKey();

        DB::beginTransaction();

        CatalogNode::ofCatalog($this)->delete();
        foreach ($positions as $position) {
            if ($position['id'] <= 0) {
                continue;
            }
            $position = [
                'catalog_id' => $id,
                'node_id' => $position['id'],
                'parent_id' => $position['parent_id'],
                'prev_id' => $position['prev_id'],
                'path' => '/'.join('/', array_slice($position['path'], 1)).'/',
            ];
            CatalogNode::create($position);
        }

        DB::commit();

        $this->touch();
    }

    /**
     * @return \App\Support\Tree
     */
    public function tree()
    {
        if (! $this->tree) {
            $this->tree = new Tree($this->getPositions());
        }
        return $this->tree;
    }


    /**
     * 获取指定节点的直接子节点
     *
     * @param array $args 指定节点
     * @return \July\Node\NodeSet
     */
    public function get_children(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->children($id));
        }

        return NodeSet::fetch($ids);
    }

    public function get_under(...$args)
    {
        return $this->get_children(...$args);
    }

    /**
     * 获取指定节点的所有子节点
     *
     * @param array $args 指定节点
     * @return \July\Node\NodeSet
     */
    public function get_descendants(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            $args = [0];
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->descendants($id));
        }

        return NodeSet::fetch($ids);
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
     * @return \July\Node\NodeSet
     */
    public function get_parent(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            if ($parent = $tree->parent($id)) {
                $ids[] = $parent;
            }
        }

        return NodeSet::fetch($ids);
    }

    public function get_over(...$args)
    {
        return $this->get_parent(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 指定节点
     * @return \July\Node\NodeSet
     */
    public function get_ancestors(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->ancestors($id));
        }

        return NodeSet::fetch($ids);
    }

    public function get_above(...$args)
    {
        return $this->get_ancestors(...$args);
    }

    /**
     * 获取指定节点的相邻节点
     *
     * @param array $args 指定节点
     * @return \July\Node\NodeSet
     */
    public function get_siblings(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return new NodeSet;
        }

        $tree = $this->tree();
        $ids = [];
        foreach ($args as $id) {
            $ids = array_merge($ids, $tree->siblings($id));
        }

        return NodeSet::fetch($ids);
    }

    public function get_around(...$args)
    {
        return $this->get_siblings(...$args);
    }

    /**
     * 在指定的树中，获取当前节点的前一个节点
     *
     * @param int $id
     * @return \July\Node\Node|null
     */
    public function get_prev($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->prev($id)) {
            return NodeSet::fetch([$id])->first();
        }

        return null;
    }

    /**
     * 在指定的树中，获取当前节点的后一个节点
     *
     * @param int $id
     * @return \July\Node\Node|null
     */
    public function get_next($id)
    {
        if (!$id) {
            return null;
        }

        if ($id = $this->tree()->next($id)) {
            return NodeSet::fetch([$id])->first();
        }

        return null;
    }

    public function get_nodes()
    {
        $ids = array_keys($this->tree()->getNodes());

        return NodeSet::fetch($ids);
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
        return collect(array_merge($this->tree()->ancestors($id), [$id]));
    }
}
