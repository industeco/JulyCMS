<?php

namespace App\ModelCollections;

use App\Models\Catalog;

class CatalogCollection extends ModelCollection
{
    public static function find($args)
    {
        if (empty($args)) {
            $catalog = Catalog::default();
            return new static([
                $catalog->truename => $catalog,
            ]);
        }

        if (! is_array($args)) {
            $args = [$args];
        }

        $items = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($catalog = Catalog::find($arg)) {
                    $items[$catalog->truename] = $catalog;
                }
            } elseif ($arg instanceof Catalog) {
                $items[$arg->truename] = $arg;
            } elseif ($arg instanceof static) {
                $items = array_merge($items, $arg->toArray());
            }
        }

        if (empty($items)) {
            $catalog = Catalog::default();
            $items[$catalog->truename] = $catalog;
        }

        return new static($items);
    }

    /**
     * 获取指定节点的直接子节点
     *
     * @param array $args 用于指定节点的参数
     * @return NodeCollection
     */
    public function get_children(...$args)
    {
        return $this->first()->get_children(...$args);
    }

    public function get_under(...$args)
    {
        return $this->get_children(...$args);
    }

    /**
     * 获取指定节点的所有子节点
     *
     * @param array $args 用于指定节点的参数
     * @return NodeCollection
     */
    public function get_descendants(...$args)
    {
        return $this->first()->get_descendants(...$args);
    }

    public function get_below(...$args)
    {
        return $this->get_descendants(...$args);
    }

    /**
     * get_parents 别名
     * 获取指定节点的所有上级节点
     *
     * @param array $args 用于指定节点的参数
     * @return NodeCollection
     */
    public function get_parent(...$args)
    {
        return $this->first()->get_parent(...$args);
    }

    public function get_over(...$args)
    {
        return $this->get_parent(...$args);
    }

    /**
     * 获取指定节点的所有上级节点
     *
     * @param array $args 用于指定节点的参数
     * @return NodeCollection
     */
    public function get_ancestors(...$args)
    {
        return $this->first()->get_ancestors(...$args);
    }

    public function get_above(...$args)
    {
        return $this->get_ancestors(...$args);
    }

    /**
     * 获取指定节点的相邻节点
     *
     * @param Tree|TreeCollection|null $tree
     * @return NodeCollection
     */
    public function get_siblings(...$args)
    {
        return $this->first()->get_siblings(...$args);
    }

    public function get_around(...$args)
    {
        return $this->get_siblings(...$args);
    }

    /**
     * 获取指定节点的路径（节点 id 集合）
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function get_path($id)
    {
        return $this->first()->get_path($id);
    }

    /**
     * 在指定的树中，获取当前节点的前一个节点
     *
     * @param int $id
     * @return \App\Models\Node
     */
    public function get_prev($id)
    {
        return $this->first()->get_prev($id);
    }

    /**
     * 在指定的树中，获取当前节点的后一个节点
     *
     * @param int $id
     * @return \App\Models\Node
     */
    public function get_next($id)
    {
        return $this->first()->get_next($id);
    }
}
