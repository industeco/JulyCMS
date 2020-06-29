<?php

namespace App\TwigExtensions;

use App\Models;
use App\ModelCollections\CatalogCollection;
use App\ModelCollections\ContentCollection;
use App\ModelCollections\ContentTypeCollection;
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

            // 获取内容集
            new TwigFunction('contents', [$this, 'contents']),

            // 获取类型集
            new TwigFunction('types', [$this, 'content_types']),

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

            // 使用 tags 过滤节点集
            new TwigFilter('tags', function($contents, array $options = []) {
                if ($contents instanceof ContentCollection && !empty($options)) {
                    $match = array_pop($options);
                    if (!is_int($match)) {
                        $options[] = $match;
                        $match = 1;
                    }

                    $options = collect($options)->flatten()->all();
                    if (!empty($options)) {
                        return $contents->match_tags($options, $match);
                    }
                }

                return $contents;
            }, ['is_variadic' => true]),

            // 按内容类型过滤节点集
            new TwigFilter('types', function($contents, array $options = []) {

                if ($contents instanceof ContentCollection) {
                    if (count($options) === 1 && is_array($options[0])) {
                        $options = $options[0];
                    }
                    if (!empty($options)) {
                        return $contents->filter(function($content) use($options) {
                            return in_array($content->content_type, $options);
                        })->keyBy('id');
                    }
                }

                return $contents;
            }, ['is_variadic' => true]),
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
     * @return \App\ModelCollections\ContentCollection
     */
    public function contents(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return ContentCollection::findAll();
        }
        return ContentCollection::find($args);
    }

    /**
     * 获取类型集
     *
     * @param string|int|array $args 用于获取类型的参数，可以是：
     *  - 类型
     *  - 类型真名 (truename)
     *
     * @return \App\ModelCollections\ContentTypeCollection
     */
    public function content_types(...$args)
    {
        $args = format_arguments($args);
        if (empty($args)) {
            return ContentTypeCollection::findAll();
        }
        return ContentTypeCollection::find($args);
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
