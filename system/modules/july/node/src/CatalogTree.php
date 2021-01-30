<?php

namespace July\Node;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CatalogTree
{
    /**
     * @var \July\Node\Catalog
     */
    protected $catalog;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $treeNodes;

    /**
     * @param  \July\Node\Catalog $catalog
     */
    public function __construct(Catalog $catalog)
    {
        $this->catalog = $catalog;

        $this->treeNodes = collect($this->getTreeNodes());
    }

    /**
     * 从目录记录生成树节点
     *
     * @return array
     */
    protected function getTreeNodes()
    {
        $nodes = $this->catalog->nodes->map(function(Node $node) {
            return [
                'node_id' => $node->pivot->node_id,
                'parent_id' => $node->pivot->parent_id ?? 0,
                'prev_id' => $node->pivot->prev_id,
                'next_id' => null,
                'child_id' => null,
                'children' => [],
                'path' => array_map('intval', explode('/', trim($node->pivot->path, '/'))),
            ];
        })->keyBy('node_id')->all();

        $root = [
            'node_id' => 0,
            'parent_id' => null,
            'prev_id' => null,
            'next_id' => null,
            'child_id' => null,
            'children' => [],
            'path' => [],
        ];

        if (empty($nodes)) {
            return [0 => $root];
        }

        if (count($nodes) === 1) {
            $node = array_shift($nodes);
            $id = $node['node_id'];

            $node['parent_id'] = 0;
            $node['prev_id'] = null;
            $node['next_id'] = null;
            $node['path'] = [0];
            $treeNodes[$id] = $node;

            $treeNodes[0]['child_id'] = $id;
            $treeNodes[0]['children'] = [$id];

            return $treeNodes;
        }

        try {
            [$nodes, $first] = $this->prepareNodes($nodes);
            $sorted = $this->sortNodes($nodes, $first);
            if (count($sorted) === count($nodes)) {
                Log::info('Nodes are correct.');
                return $nodes;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        Log::info('Nodes not correct.');
        $nodes = $this->correctNodes($nodes);
        $first = $nodes[0]['child_id'];
        return $this->sortNodes($nodes, $first);
    }

    protected function prepareNodes(array $nodes)
    {
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
        return $nodes;
    }

    /**
     * 排序节点
     *
     * @param  array $nodes 待排序的节点
     */
    protected function sortNodes(array $nodes)
    {
        $sorted = [];
        $node = $nodes[0];

        // 防止陷入死循环
        $limit = count($nodes);
        $counter = 0;
        while (true) {
            $counter++;
            if ($counter > $limit + 3) {
                break;
            }
            if (! isset($sorted[$node['node_id']])) {
                $sorted[$node['node_id']] = $node;

                // 防止陷入死循环
                $limit -= 1;
                $counter = 0;
                if ($node['child_id']) {
                    $node = $nodes[$node['child_id']];
                    continue;
                }
            }

            if ($node['next_id']) {
                $node = $nodes[$node['next_id']];
            } elseif ($node['parent_id']) {
                $node = $nodes[$node['parent_id']];
            } else {
                break;
            }
        }

        return $sorted;
    }

    /**
     * 节点信息纠错，在默认方法出错时执行
     *
     * @param  array $nodes
     * @return array
     */
    protected function correctNodes(array $nodes)
    {
        return $this->correntOrders($this->correctParent($nodes));
    }

    /**
     * 纠正 parent_id 和 path（假设存在冲突或缺失）
     *
     * @param  array $nodes
     * @return array
     */
    protected function correctParent(array $nodes)
    {
        foreach ($nodes as $id => $node) {
            if (!$node['parent_id'] || !isset($nodes[$node['parent_id']])) {
                $nodes[$id]['parent_id'] = 0;
            }
        }

        $orders = [
            0 => [
                'node_id' => 0,
                'parent_id' => null,
                'prev_id' => null,
                'next_id' => null,
                'child_id' => null,
                'children' => [],
                'path' => [],
            ],
        ];
        $parents = [0 => true];
        while (true) {
            $count = 0;
            $children = [];
            foreach ($nodes as $id => $node) {
                $parent_id = $node['parent_id'];
                if (isset($parents[$parent_id])) {
                    $orders[$parent_id]['children'][] = $id;
                    $node['parent_id'] = $parent_id;
                    $node['path'] = array_merge($orders[$parent_id]['path'], [$parent_id]);
                    $orders[$id] = $node;
                    $children[$id] = true;
                    $count++;
                }
            }
            if ($count > 0) {
                $nodes = array_diff_key($nodes, $orders);
            } else {
                $node = array_shift($nodes);
                $node['parent_id'] = 0;
                $node['path'] = [0];
                $orders[$node['node_id']] = $node;
                $orders[0]['children'][] = $node['node_id'];
            }
            if (empty($nodes)) {
                break;
            }
            $parents = $children;
        }

        return $orders;
    }

    /**
     * 纠正 prev_id（假设存在冲突或缺失）
     *
     * @param  array $nodes
     * @return array
     */
    protected function correntOrders(array $nodes)
    {
        foreach ($nodes as $id => $node) {
            if (!$node['prev_id']) {
                continue;
            }
            $prev_id = $node['prev_id'];
            if (!isset($nodes[$prev_id]) || $nodes[$prev_id]['parent_id'] !== $node['parent_id']) {
                $nodes[$id]['prev_id'] = null;
            }
        }

        foreach ($nodes as $id => $node) {
            $children = $this->correctChildrenOrders($nodes, $node['children']);
            $prev = null;
            foreach ($children as $id) {
                $nodes[$id]['prev_id'] = $prev;
                if ($prev) {
                    $nodes[$prev]['next_id'] = $id;
                }
                $prev = $id;
            }
            $nodes[$id]['children'] = $children;
            $nodes[$id]['child_id'] = $children[0] ?? null;
        }

        return $nodes;
    }

    /**
     * 纠正子节点顺序
     *
     * @param  array $nodes
     * @param  array $children
     * @return array
     */
    protected function correctChildrenOrders(array $nodes, array $children)
    {
        if (count($children) <= 1) {
            return $children;
        }

        $orders = [];
        $prev = null;
        while (true) {
            $count = 0;
            foreach ($children as $id) {
                if ($nodes[$id]['prev_id'] === $prev) {
                    $orders[] = $id;
                    $prev = $id;
                    $count++;
                }
            }
            if ($count > 0) {
                $children = array_diff($children, $orders);
            } else {
                $prev = array_shift($children);
                $orders[] = $prev;
            }
            if (empty($children)) {
                break;
            }
        }
        return $orders;
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
            return $this->treeNodes->only($ids)->keys()->filter()->all();
        }
        return $this->treeNodes->keys()->filter()->all();
    }

    /**
     * 获取给定节点的父节点
     *
     * @param int $id
     * @return array
     */
    public function parent($id)
    {
        if ($node = $this->treeNodes->get($id)) {
            if ($node['parent_id']) {
                return [$node['parent_id']];
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
        if ($node = $this->treeNodes->get($id)) {
            return $node['path'];
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
        if ($this->treeNodes->has($id)) {
            foreach ($this->treeNodes as $node_id => $node) {
                if ($node_id && $node['parent_id'] == $id) {
                    $children[] = $node_id;
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
            return $this->treeNodes->keys()->filter()->all();
        }

        $descendants = [];
        if ($this->treeNodes->has($id)) {
            $end = false;
            foreach ($this->treeNodes as $node_id => $node) {
                if ($end) {
                    if ($node_id == $end) {
                        break;
                    } else {
                        $descendants[] = $node_id;
                    }
                }
                if ($node_id == $id) {
                    $end = $node['next_id'] ?? -1;
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
        if ($id > 0 && ($node = $this->treeNodes->get($id))) {
            $parent_id = $node['parent_id'];
            foreach ($this->treeNodes as $node_id => $node) {
                if ($node['parent_id'] == $parent_id && $node_id != $id) {
                    $siblings[] = $node_id;
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
        if ($node = $this->treeNodes->get($id)) {
            return $node['prev_id'] ?? null;
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
        if ($node = $this->treeNodes->get($id)) {
            return $node['next_id'] ?? null;
        }
        return null;
    }
}
