<?php

namespace App\ModelCollections;

use App\Models\Node;
use App\Contracts\GetNodes;

class NodeCollection extends ModelCollection
{
    /**
     * 获取节点集
     *
     * @param mixed $args 用于获取节点的参数，可以是：
     *  - 节点
     *  - 节点 id
     *  - 节点集
     *  - 节点类型集
     *  - 节点目录集
     *  - 节点标签集
     * 或以上类型组成的数组
     *
     * @return \App\ModelCollections\NodeCollection
     */
    public static function find($args)
    {
        if (empty($args)) {
            return new static(Node::fetchAll()->keyBy('id'));
        }

        if (! is_array($args)) {
            $args = [$args];
        }

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

        return new static($items);
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
