<?php

namespace July\Node\TwigExtensions;

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
}
