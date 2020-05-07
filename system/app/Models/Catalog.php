<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Node;
use App\Casts\Json;
use App\ModelIsomers\CatalogTree;
use App\Contracts\GetNodes;
use App\Contracts\HasModelConfig;
use App\ModelCollections\NodeCollection;
use App\Traits\CastModelConfig;

class Catalog extends JulyModel implements GetNodes, HasModelConfig
{
    use CastModelConfig;

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
    protected $primaryKey = 'truename';

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
     * 不可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = [
        'truename',
        'is_preset',
        // 'langcode',
        'config',
        // 'updated_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_preset' => 'boolean',
        'config' => Json::class,
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
                ->withPivot(
                    'parent_id',
                    'prev_id'
                );
    }

    public function nodesMerged()
    {
        $nodes = [];
        foreach ($this->nodes as $node) {
            $values = $node->getData();
            $values['parent_id'] = $node->pivot->parent_id;
            $values['prev_id'] = $node->pivot->prev_id;
            $nodes[] = $values;
        }
        return $nodes;
    }

    public function configStructure(): array
    {
        return [
            'name' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
            'description' => [
                'type' => 'interface_value',
                'cast' => 'string',
            ],
        ];
    }

    // /**
    //  * 保存前对请求数据进行处理
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @param \App\Models\Catalog $catalog
    //  * @return Array
    //  */
    // public static function prepareRequest(Request $request, Catalog $catalog = null)
    // {
    //     $ilang = langcode('interface_value');
    //     $config = [
    //         'interface_values' => [
    //             'name' => [
    //                 $ilang => $request->input('name'),
    //             ],
    //             'description' => [
    //                 $ilang => $request->input('description'),
    //             ],
    //         ],
    //     ];

    //     if ($catalog) {
    //         return [
    //             'config' => array_replace_recursive($catalog->config, $config),
    //         ];
    //     }

    //     $clang = langcode('content_value');
    //     $config['langcode'] = [
    //         'interface_value' => $ilang,
    //         'content_value' => $clang,
    //     ];

    //     return [
    //         'truename' => $request->input('truename'),
    //         'config' => $config,
    //     ];
    // }

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
        $this->cacheClear($this->id.'/catalogNodes');
        $this->cacheClear($this->id.'/treeNodes');

        // DB::delete("DELETE from catalog_node where `catalog`=? and (`node_id`=? or `path` like '%/$node_id/%' )");
        CatalogNode::where([
            'catalog' => $this->truename,
            'node_id' => $position['node_id'],
        ])->orWhere([
            ['catalog', '=', $this->truename],
            ['path', 'like', '%/'.$position['node_id'].'/%'],
        ])->delete();

        $this->forceUpdate();
    }

    public function insertPosition(array $position)
    {
        $this->cacheClear($this->id.'/catalogNodes');
        $this->cacheClear($this->id.'/treeNodes');

        // $position['catalog'] = $this->truename;
        $position['langcode'] = langcode('content_value');

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

        $this->forceUpdate();
    }

    public function updatePositions(array $positions)
    {
        $this->cacheClear($this->id.'/catalogNodes');
        $this->cacheClear($this->id.'/treeNodes');

        $supplement = [
            'catalog' => $this->truename,
            'langcode' => langcode('content_value'),
        ];
        foreach ($positions as $index => $position) {
            $positions[$index] = array_merge($position, $supplement);
        }

        DB::table('catalog_node')->where('catalog', $this->truename)->delete();
        DB::transaction(function() use ($positions) {
            DB::table('catalog_node')->insert($positions);
        });

        $this->forceUpdate();
    }

    public function retrieveCatalogNodes()
    {
        $cacheid = $this->id.'/catalogNodes';
        if ($nodes = $this->cacheGet($cacheid)) {
            $nodes = $nodes['value'];
        } else {
            $nodes = CatalogNode::where('catalog', $this->truename)
                ->get(['node_id','parent_id','prev_id','path'])->toArray();

            $this->cachePut($cacheid, $nodes);
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

    public function get_nodes(): NodeCollection
    {
        $ids = $this->tree()->nodes();
        return NodeCollection::find($ids);
    }
}
