<?php

namespace July\Core\Node;

use App\Utils\Pocket;
use Illuminate\Support\Collection;

class CatalogTree
{
    /**
     * @var \July\Node\Catalog
     */
    protected $catalog = null;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $nodes = null;

    function __construct(Catalog $catalog)
    {
        $this->catalog = $catalog;

        $pocket = new Pocket($catalog);
        $key = $pocket->key('treeNodes');

        if ($treeNodes = $pocket->get($key)) {
            $treeNodes = $treeNodes->value;
        }else {
            $treeNodes = $this->getTreeNodes();
            $pocket->put($key, $treeNodes);
        }
        $this->nodes = collect($treeNodes);
    }

    protected function getTreeNodes()
    {
        $nodes = $this->catalog->pocketCatalogNodes();

        $treeNodes = [
            0 => [
                'node_id' => 0,
                'parent_id' => null,
                'prev_id' => null,
                'path' => [],
            ],
        ];

        if (empty($nodes)) {
            return $treeNodes;
        }

        if (count($nodes) === 1) {
            $node = reset($nodes);
            $node['path'] = array_values(array_filter(explode('/', $node['path'])));
            $treeNodes[$node['node_id']] = $node;

            return $treeNodes;
        }

        // $nodes = $this->sortNodes($nodes);
        foreach ($this->sortNodes($nodes) as $id => $node) {
            $node['parent_id'] = $node['parent_id'] ?? 0;
            $treeNodes[$id] = $node;
        }

        return $treeNodes;
    }

    protected function sortNodes(array $nodes)
    {
        $nodes = collect($nodes)->map(function($node) {
            $path = array_values(array_filter(explode('/', $node['path'])));
            return [
                'node_id' => (int) $node['node_id'],
                'parent_id' => (int) $node['parent_id'],
                'prev_id' => (int) $node['prev_id'],
                'path' => array_map('intval', $path),
            ];
        })->keyBy('node_id')->toArray();

        $first = null;
        foreach ($nodes as $id => $node) {
            if ($node['prev_id']) {
                $nodes[$node['prev_id']]['next_id'] = $id;
            } elseif ($node['parent_id']) {
                $nodes[$node['parent_id']]['child_id'] = $id;
            } elseif (!$first) {
                $first = $id;
            }
        }

        $sortedNodes = [];
        $node = $nodes[$first];
        while (true) {
            if (! isset($sortedNodes[$node['node_id']])) {
                $sortedNodes[$node['node_id']] = $node;
                if ($node['child_id'] ?? null) {
                    $node = $nodes[$node['child_id']];
                    continue;
                }
            }

            if ($node['next_id'] ?? null) {
                $node = $nodes[$node['next_id']];
            } elseif ($node['parent_id']) {
                $node = $nodes[$node['parent_id']];
            } else {
                break;
            }
        }

        return $sortedNodes;
    }

    /**
     * 获取所有节点，非嵌套
     *
     * @param array $ids
     * @return
     */
    public function nodes(array $ids = null)
    {
        if ($ids) {
            return $this->nodes->only($ids)->keys()->filter()->all();
        }
        return $this->nodes->keys()->filter()->all();
    }

    /**
     * 获取给定节点的父节点
     *
     * @param int $id
     * @return array
     */
    public function parent($id)
    {
        if ($content = $this->nodes->get($id)) {
            if ($content['parent_id']) {
                return [$content['parent_id']];
            }
        }
        return [];
    }

    /**
     * 获取给定节点的祖先节点
     *
     * @param int $id
     * @return array
     */
    public function ancestors($id)
    {
        if ($content = $this->nodes->get($id)) {
            return $content['path'];
        }
        return [];
    }

    /**
     * 获取给定节点的直接子节点
     *
     * @param int $id
     * @return array
     */
    public function children($id)
    {
        $children = [];
        if ($this->nodes->has($id)) {
            foreach ($this->nodes as $content_id => $content) {
                if ($content_id && $content['parent_id'] == $id) {
                    $children[] = $content_id;
                }
            }
        }
        return $children;
    }

    /**
     * 获取给定节点的子孙节点
     *
     * @param int $id
     * @return array
     */
    public function descendants($id)
    {
        if ($id == 0) {
            return $this->nodes->keys()->filter()->all();
        }

        $descendants = [];
        if ($this->nodes->has($id)) {
            $end = false;
            foreach ($this->nodes as $content_id => $content) {
                if ($end) {
                    if ($content_id == $end) {
                        break;
                    } else {
                        $descendants[] = $content_id;
                    }
                }
                if ($content_id == $id) {
                    $end = $content['next_id'] ?? -1;
                    continue;
                }
            }
        }

        return $descendants;
    }

    /**
     * 获取给定节点的同级节点，不包含自身
     *
     * @param int $id
     * @return array
     */
    public function siblings($id)
    {
        $siblings = [];
        if ($id > 0 && ($content = $this->nodes->get($id))) {
            $parent_id = $content['parent_id'];
            foreach ($this->nodes as $content_id => $content) {
                if ($content['parent_id'] == $parent_id && $content_id != $id) {
                    $siblings[] = $content_id;
                }
            }
        }

        return $siblings;
    }

    /**
     * 获取给定节点的前一个节点
     *
     * @param int $id
     * @return int
     */
    public function prev($id)
    {
        if ($content = $this->nodes->get($id)) {
            return $content['prev_id'] ?? null;
        }
        return null;
    }

    /**
     * 获取给定节点的后一个节点
     *
     * @param int $id
     * @return int
     */
    public function next($id)
    {
        if ($content = $this->nodes->get($id)) {
            return $content['next_id'] ?? null;
        }
        return null;
    }
}
