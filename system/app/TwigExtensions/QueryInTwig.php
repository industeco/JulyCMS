<?php

namespace App\TwigExtensions;

use App\Models;
use App\ModelCollections\CatalogCollection;
use App\ModelCollections\NodeCollection;
use App\ModelCollections\NodeTypeCollection;
use App\ModelCollections\TagCollection;
use Illuminate\Support\Str;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class QueryInTwig extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            '_host' => config('jc.url'),
            '_email' => config('jc.email'),
            '_catalog' => Models\Catalog::default(),
        ];
    }

    public function getFunctions()
    {
        return [
            // 获取配置
            new TwigFunction('config', function ($key) {
                return config($key) ?? config('jc.' . $key) ?? config('app.' . $key) ?? null;
            }),

            // 获取节点集
            new TwigFunction('nodes', [$this, 'nodes']),

            // 获取类型集
            new TwigFunction('types', [$this, 'node_types']),

            // 获取目录集
            new TwigFunction('catalogs', [$this, 'catalogs']),

            // 获取标签集
            new TwigFunction('tags', [$this, 'tags']),
        ];
    }

    public function getFilters()
    {
        return [
            // html_id 方法用于将字符串转换为可用做 HTML 元素 id 的形式
            new TwigFilter('html_id', function ($input) {

                $id = preg_replace('/\s+|[^\w\-]/', '_', trim($input));

                if (!$id) {
                    return 'jc_' . Str::random(5);
                }

                return $id;
            }),

            // html_class 方法用于将字符串转换为可用做 HTML 元素 class 的形式
            new TwigFilter('html_class', function ($input) {

                $class = preg_replace('/\s+|[^\w\-]/', '_', trim($input));

                if (!$class) {
                    return 'jc-' . Str::random(5);
                }

                return $class;
            }),
        ];
    }

    /**
     * 获取节点集
     *
     * @param int|array $args 用于获取节点的参数，可以是：
     *  - 节点
     *  - 节点 id
     *  - 类型集
     *  - 目录集
     *  - 标签集
     *
     * @return \App\ModelCollections\NodeCollection
     */
    public function nodes(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return NodeCollection::findAll();
        }
        return NodeCollection::find($args);
    }

    /**
     * 获取类型集
     *
     * @param string|int|array $args 用于获取类型的参数，可以是：
     *  - 类型
     *  - 类型真名 (truename)
     *
     * @return \App\ModelCollections\NodeTypeCollection
     */
    public function node_types(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return NodeTypeCollection::findAll();
        }
        return NodeTypeCollection::find($args);
    }

    /**
     * 获取节点树集
     *
     * @param string|array $args 用于获取目录的参数，可以是：
     *  - 目录
     *  - 目录真名 (truename)
     *
     * @return \App\ModelCollections\CatalogCollection
     */
    public function catalogs(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return CatalogCollection::findAll();
        }
        return CatalogCollection::find($args);
    }

    /**
     * 获取标签集
     *
     * @param string|array $args 用于获取标签的参数，可以是：
     *  - 标签
     *  - 标签名
     *
     * @return \App\ModelCollections\TagCollection
     */
    public function tags(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return TagCollection::findAll();
        }
        return TagCollection::find($args);
    }
}
