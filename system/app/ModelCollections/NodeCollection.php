<?php

namespace App\ModelCollections;

use App\Models\Node;
use App\Contracts\GetNodes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class NodeCollection extends ModelCollection
{
    protected static $model = Node::class;
    protected static $primaryKey = 'id';

    public static function findArray(array $args)
    {
        $items = [];
        foreach ($args as $arg) {
            // 节点 id
            if (is_numeric($arg)) {
                if ($node = Node::fetch($arg)) {
                    $items[$node->id] = $node;
                }
            }

            // 节点对象
            elseif ($arg instanceof Node) {
                $items[$arg->id] = $arg;
            }

            elseif ($arg instanceof static) {
                $items = array_merge($items, $arg->all());
            }

            // 类型集，标签集等对象
            elseif ($arg instanceof GetNodes) {
                $items = array_merge($items, $arg->get_nodes()->keyBy('id')->all());
            }
        }

        return (new static($items))->keyBy('id');
    }

    /**
     * 在指定的树中，获取当前节点集的直接子节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_children($catalog = null)
    {
        $ids = $this->pluck('id')->all();
        CatalogCollection::find($catalog)->get_children(...$ids);
    }

    public function get_under($catalog = null)
    {
        return $this->get_children($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的所有子节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_descendants($catalog = null)
    {
        $ids = $this->pluck('id')->all();
        CatalogCollection::find($catalog)->get_descendants(...$ids);
    }

    public function get_below($catalog = null)
    {
        return $this->get_descendants($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的直接父节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_parent($catalog = null)
    {
        $ids = $this->pluck('id')->all();
        CatalogCollection::find($catalog)->get_parent(...$ids);
    }

    public function get_over($catalog = null)
    {
        return $this->get_parent($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的所有上级节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_ancestors($catalog = null)
    {
        $ids = $this->pluck('id')->all();
        CatalogCollection::find($catalog)->get_ancestors(...$ids);
    }

    public function get_above($catalog = null)
    {
        return $this->get_ancestors($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的相邻节点
     *
     * @param mixed $catalog
     * @return NodeCollection
     */
    public function get_siblings($catalog = null)
    {
        $ids = $this->pluck('id')->all();
        CatalogCollection::find($catalog)->get_siblings(...$ids);
    }

    public function get_around($catalog = null)
    {
        return $this->get_siblings($catalog);
    }

    public function get_types()
    {
        $types = $this->pluck('node_type')->unique()->all();
        return NodeTypeCollection::find($types);
    }

    public function get_tags()
    {
        $ids = $this->pluck('id')->unique()->all();
        $langcode = config('current_render_langcode') ?? langcode('site_page');

        $tags = DB::table('node_tag')
            ->whereIn('node_id', $ids)
            ->where('langcode', $langcode)
            ->get('tag')->pluck('tag')->all();

        return TagCollection::find($tags);
    }

    public function get_catalog()
    {
        $types = $this->pluck('node_type')->unique()->all();
        return NodeTypeCollection::find($types);
    }

    public function match_tags(array $tags, $matches = null)
    {
        $nodes = TagCollection::find($tags)->match($matches)->get_nodes();
        return $this->only($nodes->pluck('id'));
    }

    // /**
    //  * 在指定的树中，获取当前节点的相邻节点
    //  *
    //  * @param Tree|TreeCollection|null $tree
    //  * @return NodeCollection
    //  */
    // public function get_path($tree = null)
    // {
    //     $anchors = $this->pluck('id')->all();
    //     return Tree::resolve($tree)->get_path($anchors);
    // }

    // /**
    //  * 在指定的引用空间中，获取所有引用过当前节点集节点的主节点
    //  *
    //  * @param string $field 字段机读名
    //  * @return NodeCollection
    //  */
    // public function get_hosts($field = null)
    // {
    //     $anchors = $this->pluck('id')->all();
    //     return NodeReference::host_nodes($anchors, $field);
    // }
}
