<?php

namespace July\Node\TwigExtensions;

use July\Node\Catalog;
use July\Node\CatalogSet;
use July\Node\NodeSet;
use July\Node\NodeTypeSet;

class NodeMixin
{
    /**
     * 获取节点集
     *
     * @param array $args 用于获取节点的参数，可以是：
     *  - 节点 id
     *  - 节点对象
     *
     * @return \July\Node\NodeSet
     */
    public function get_nodes()
    {
        return function(...$args) {
            $args = real_args($args);
            if (empty($args)) {
                return NodeSet::fetchAll();
            }
            return NodeSet::fetch($args);
        };
    }

    /**
     * 获取类型集
     *
     * @param array $args 用于获取节点类型的参数，可以是：
     *  - 节点类型 id
     *  - 节点类型对象
     *
     * @return \July\Node\NodeTypeSet
     */
    public function get_molds()
    {
        return function(...$args) {
            $args = real_args($args);
            if (empty($args)) {
                return NodeTypeSet::fetchAll();
            }
            return NodeTypeSet::fetch($args);
        };
    }

    /**
     * 获取节点树集
     *
     * @param array $args 用于获取节点目录的参数，可以是：
     *  - 节点目录 id
     *  - 节点目录对象
     *
     * @return \July\Node\CatalogSet
     */
    public function get_catalogs()
    {
        return function(...$args) {
            $args = real_args($args);
            if (empty($args)) {
                return CatalogSet::fetchAll();
            }
            return CatalogSet::fetch($args);
        };
    }

    /**
     * 判断指定节点是否在当前路径中
     *
     * @param int|string|\July\Node\Node $node_id
     * @return bool
     */
    public function in_path()
    {
        return function($node_id) {
            if (is_object($node_id) && ($node_id instanceof \July\Node\Node)) {
                $node_id = $node_id->getKey();
            }
            if (! is_numeric($node_id)) {
                return false;
            }
            $node_id = intval($node_id);

            /** @var \App\Support\JustInTwig */
            $jit = $this;

            $path = $jit->getGlobal('_path');
            if (!is_array($path)) {
                return false;
            }

            $current = $jit->getGlobal('_node');
            if (!$current || !($current instanceof \July\Node\Node)) {
                return false;
            }

            return $node_id === $current->getKey() || in_array($node_id, $path);
        };
    }

    public function get_columns()
    {
        return function($catalog = null) {
            return CatalogSet::fetch($catalog)->get_under(0);
        };
    }
}
