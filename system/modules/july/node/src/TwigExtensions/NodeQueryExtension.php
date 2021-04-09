<?php

namespace July\Node\TwigExtensions;

use Illuminate\Support\Str;
use July\Message\MessageForm;
use July\Node\Catalog;
use July\Node\CatalogSet;
use July\Node\NodeSet;
use July\Node\NodeTypeSet;
use July\Taxonomy\TermSet;
use Specs\Spec;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class NodeQueryExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            '_catalog' => Catalog::default(),
        ];
    }

    public function getFunctions()
    {
        return [
            // 获取配置
            new TwigFunction('config', function ($key) {
                return config($key) ?? config('app.'.$key) ?? null;
            }),

            // 获取内容集
            new TwigFunction('nodes', [$this, 'nodes']),

            // 获取类型集
            new TwigFunction('molds', [$this, 'molds']),

            // 获取目录集
            new TwigFunction('catalogs', [$this, 'catalogs']),

            // 获取消息表单
            new TwigFunction('forms', [$this, 'forms']),

            // 获取规格类型
            new TwigFunction('specs', [$this, 'specs']),

            // 获取路由短网址
            new TwigFunction('short_url', [$this, 'short_url']),
        ];
    }

    public function getFilters()
    {
        return [
            // html_id 方法用于将字符串转换为可用做 HTML 元素 id 的形式
            new TwigFilter('html_id', function ($input) {
                $id = preg_replace('/\s+|[^\w\-]/', '_', trim($input));
                return $id ?: 'jc_' . Str::random(5);
            }),

            // html_class 方法用于将字符串转换为可用做 HTML 元素 class 的形式
            new TwigFilter('html_class', function ($input) {
                $class = preg_replace('/\s+|[^\w\-]/', '_', trim($input));
                return $class ?: 'jc-' . Str::random(5);
            }),

            // 按内容类型过滤节点集
            new TwigFilter('molds', function($nodes, array $options = []) {
                if ($nodes instanceof NodeSet) {
                    if (count($options) === 1 && is_array($options[0])) {
                        $options = $options[0];
                    }
                    if (!empty($options)) {
                        return $nodes->filter(function($node) use($options) {
                            return in_array($node->node_type_id, $options);
                        })->keyBy('id');
                    }
                }
                return $nodes;
            }, ['is_variadic' => true]),
        ];
    }

    /**
     * 获取节点集
     *
     * @param array $args 用于获取节点的参数，可以是：
     *  - 节点 id
     *  - 节点对象
     *
     * @return \July\Node\NodeSet
     */
    public function nodes(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return NodeSet::fetchAll();
        }
        return NodeSet::fetch($args);
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
    public function molds(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return NodeTypeSet::fetchAll();
        }
        return NodeTypeSet::fetch($args);
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
    public function catalogs(...$args)
    {
        $args = real_args($args);
        if (empty($args)) {
            return CatalogSet::fetchAll();
        }
        return CatalogSet::fetch($args);
    }

    /**
     * 获取联系表单对象
     *
     * @param  string $form 表单 id
     * @return \July\Message\MessageForm
     */
    public function forms($form)
    {
        return MessageForm::find($form) ?? MessageForm::default();
    }

    /**
     * 获取规格类型
     *
     * @param  string $id 规格类型 id
     * @return \Specs\Spec
     */
    public function specs($id)
    {
        return Spec::find($id);
    }

    /**
     * 获取路由短网址
     *
     * @param  string $name 路由名
     * @param  array $parameters 路由参数
     * @return \Specs\Spec
     */
    public function short_url($name, ...$parameters)
    {
        return short_url($name, $parameters);
    }
}
