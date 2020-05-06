<?php

namespace App\TwigExtensions;

use App\Models;
use App\ModelCollections\CatalogCollection;
use App\ModelCollections\NodeCollection;
use App\ModelCollections\NodeTypeCollection;
use Illuminate\Support\Str;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class ContentsQuery extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        return [
            '_host' => config('app.url'),
            '_email' => config('mail.to.address'),
            '_catalog' => Models\Catalog::default(),
        ];
    }

    public function getFunctions()
    {
        return [
            // 获取配置
            new TwigFunction('config', function ($key) {
                return config($key) ?? config('july.' . $key) ?? config('app.' . $key) ?? null;
            }),

            // 格式化网址
            new TwigFunction('url', [$this, 'url']),

            // 格式化媒体地址（图片，PDF等）
            new TwigFunction('src', [$this, 'src']),

            // 获取节点集
            new TwigFunction('nodes', [$this, 'nodes']),

            // 获取类型集
            new TwigFunction('types', [$this, 'node_types']),

            // 获取目录集
            new TwigFunction('catalogs', [$this, 'catalogs']),

            // 获取标签集
            // new TwigFunction('tags', [$this, 'tags']),
        ];
    }

    public function getFilters()
    {
        return [
            // html_id 方法用于将字符串转换为可用做 HTML 元素 id 的形式
            new TwigFilter('html_id', function ($input) {

                $id = preg_replace('/\s+|[^\w\-]/', '_', trim($input));

                if (!$id) {
                    return 'id_' . Str::random(6);
                }

                return $id;
            }),

            // html_class 方法用于将字符串转换为可用做 HTML 元素 class 的形式
            new TwigFilter('html_class', function ($input) {

                $class = preg_replace('/\s+|[^\w\-]/', '_', trim($input));

                if (!$class) {
                    return 'class_' . Str::random(6);
                }

                return $class;
            }),
        ];
    }

    /**
     * 格式化网址
     *
     * @param string $path 网址
     * @param string $format 格式化方式：absolute, relative, full
     * @return string
     */
    public function url($path)
    {
        return url($path);
    }

    /**
     * 格式化媒体资源（图片、PDF等）的地址
     *
     * @param string $path 媒体资源（图片、PDF等）的地址
     * @param string $format 格式化方式：absolute, relative, full
     * @return string
     */
    public function src($path)
    {
        return $path;
    }

    /**
     * 格式化路径为绝对路径，相对路径或全路径（以域名开头）
     *
     * @param string $path 待格式化的路径
     * @param string $format 格式化方式
     * @return string
     */
    protected function formatPath($path, $format)
    {
        $path = ltrim($path, '/');
        switch($format)
        {
            case 'absolute':
                return '/' . $path;

            case 'relative':
                return $path;

            case 'full':
                return url($path);
        }
        return $path;
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
        return NodeCollection::find($this->args($args));
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
        return NodeTypeCollection::find($this->args($args));
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
        return CatalogCollection::find($this->args($args));
    }

    protected function args(array $args)
    {
        // 如果只有一个参数，而且是一个数组，则假设该数组是使用者真正想要指定的参数数组
        if (count($args) === 1 && is_array($args[0])) {
            $args = $args[0];
        }
        return $args;
    }

    // /**
    //  * 获取标签集
    //  *
    //  * @param string|int|array $args 用于获取标签的参数，可以是：
    //  *  - 标签
    //  *  - 标签名
    //  *  - 标签 id
    //  *
    //  * @return \App\ModelCollections\TagCollection
    //  */
    // public function tags(...$args)
    // {
    //     return TagCollection::find($args);
    // }
}
