<?php

namespace July\Node\TwigExtensions;

use July\Message\MessageForm;
use July\Node\CatalogSet;
use July\Node\NodeSet;
use July\Node\NodeTypeSet;
use Specs\Spec;

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
     * 获取联系表单对象
     *
     * @param  string $form 表单 id
     * @return \July\Message\MessageForm
     */
    public function get_form()
    {
        return function($form) {
            return MessageForm::find($form) ?? MessageForm::default();
        };
    }

    /**
     * 获取规格类型
     *
     * @param  string $id 规格类型 id
     * @return \Specs\Spec
     */
    public function get_specs()
    {
        return function($id) {
            return Spec::find($id);
        };
    }

    /**
     * 获取路由短网址
     *
     * @param  string $name 路由名
     * @param  array $parameters 路由参数
     * @return \Specs\Spec
     */
    public function short_url()
    {
        return function($name, ...$parameters) {
            return short_url($name, $parameters);
        };
    }
}
