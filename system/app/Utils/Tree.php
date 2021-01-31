<?php

namespace App\Utils;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class Tree
{
    /**
     * 初始位置数据
     *
     * @var array
     */
    protected $positions;

    /**
     * 节点数据
     *
     * @var array
     */
    protected $nodes;

    /**
     * 键名映射
     *
     * @var array
     */
    protected $keymap = [
        'id' => 'id',
        'parent_id' => 'parent_id',
        'prev_id' => 'prev_id',
    ];

    public function __construct(array $positions, array $map = [])
    {
        $this->keymap = array_merge($this->keymap, $map);

        $this->positions = $positions;

        $this->genereteNodes();
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    protected function getRootNode()
    {
        return [
            'id' => 0,
            'parent_id' => null,
            'prev_id' => null,
            'next_id' => null,
            'child_id' => null,
            'children' => [],
            'path' => [],
        ];
    }

    /**
     * 从位置数据生成树节点
     *
     * @return array
     */
    protected function genereteNodes()
    {
        // 使用位置数据初始化节点
        $this->initNodes();

        if (count($this->nodes) === 1) {
            return;
        }

        if (count($this->nodes) === 2) {
            $root = $this->nodes[0];
            $node = array_merge($root, [
                'id' => key($this->nodes),
                'parent_id' => 0,
                'path' => [0],
            ]);
            $root['child_id'] = $node['id'];

            $this->nodes = [
                0 => $root,
                $node['id'] => $node,
            ];

            return;
        }

        // 校正节点数据
        $this->correctNodes();

        // 排序节点
        $this->sortNodes();

        // try {
        //     $sorted = $this->sortPositions($this->preparePositions($this->nodes));
        //     if (error_get_last()) {
        //         throw new \UnexpectedValueException();
        //     }
        //     if (count($sorted) === count($this->nodes)) {
        //         // Log::info('Nodes are correct.');
        //         return $sorted;
        //     }
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }

        // Log::info('Nodes not correct.');
        // return $this->sortPositions($this->correctPositions($this->nodes));
    }

    /**
     * 使用位置数据初始化节点数据
     *
     * @return void
     */
    public function initNodes()
    {
        // 根节点，同时作为模板使用
        $root = $this->getRootNode();

        $this->nodes = [];
        foreach ($this->positions as $position) {
            $id = key([trim($position[$this->keymap['id']]) => null]);
            $this->nodes[$id] = array_merge($root, [
                'id' => $id,
                'parent_id' => $position[$this->keymap['parent_id']] ?? 0,
                'prev_id' => $position[$this->keymap['prev_id']] ?? null,
            ]);
        }
        $this->nodes[0] = $root;
    }

    /**
     * 排序节点
     *
     * @return void
     */
    protected function sortNodes()
    {
        $sorted = [];
        $node = $this->nodes[0];
        while (true) {
            if (! isset($sorted[$node['id']])) {
                $sorted[$node['id']] = $node;
                if ($node['child_id']) {
                    $node = $this->nodes[$node['child_id']];
                    continue;
                }
            }

            if ($node['next_id']) {
                $node = $this->nodes[$node['next_id']];
            } elseif ($node['parent_id']) {
                $node = $this->nodes[$node['parent_id']];
            } else {
                break;
            }
        }

        $this->nodes = $sorted;
    }

    /**
     * 节点信息纠错
     *
     * @return void
     */
    protected function correctNodes()
    {
        $this->correctParent();
        $this->reorderNodes();
    }

    /**
     * 纠正 parent_id 和 path（假设存在冲突或缺失）
     *
     * @return void
     */
    protected function correctParent()
    {
        $orders = [
            0 => $this->nodes[0],
        ];
        $nodes = array_diff_key($this->nodes, $orders);

        foreach ($nodes as $id => $node) {
            if (!$node['parent_id'] || !isset($nodes[$node['parent_id']])) {
                $nodes[$id]['parent_id'] = 0;
            }
        }

        $parents = [0 => 1];
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
                    $children[$id] = 1;
                    $count++;
                }
            }
            if ($count > 0) {
                $nodes = array_diff_key($nodes, $orders);
            } else {
                $node = array_shift($nodes);
                $node['parent_id'] = 0;
                $node['path'] = [0];
                $orders[$node['id']] = $node;
                $orders[0]['children'][] = $node['id'];
                $children[$node['id']] = 1;
            }
            if (empty($nodes)) {
                break;
            }
            $parents = $children;
        }

        $this->nodes = $orders;
    }

    /**
     * 纠正 prev_id（假设存在冲突或缺失）
     *
     * @return void
     */
    protected function reorderNodes()
    {
        foreach ($this->nodes as $id => $node) {
            if (!$node['prev_id']) {
                continue;
            }
            $prev_id = $node['prev_id'];
            if (!isset($this->nodes[$prev_id]) || $this->nodes[$prev_id]['parent_id'] !== $node['parent_id']) {
                $this->nodes[$id]['prev_id'] = null;
            }
        }

        foreach ($this->nodes as $id => $node) {
            $children = $this->reorderChildren($node['children']);
            $prev = null;
            foreach ($children as $child) {
                $this->nodes[$child]['prev_id'] = $prev;
                if ($prev) {
                    $this->nodes[$prev]['next_id'] = $child;
                }
                $prev = $child;
            }
            $this->nodes[$id]['children'] = $children;
            $this->nodes[$id]['child_id'] = $children[0] ?? null;
        }
    }

    /**
     * 校正子节点顺序
     *
     * @param  array $children
     * @return array
     */
    protected function reorderChildren(array $children)
    {
        if (count($children) <= 1) {
            return $children;
        }

        $orders = [];
        $prev = null;
        while (true) {
            $count = 0;
            foreach ($children as $id) {
                if ($this->nodes[$id]['prev_id'] === $prev) {
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
     * 设置 next_id，child_id 和 children（假设数据没有冲突或缺失）
     *
     * @param  array $nodes
     * @return array
     */
    protected function preparePositions(array $nodes)
    {
        // 设置 next_id 和 child_id
        foreach ($nodes as $id => $node) {
            if ($node['prev_id']) {
                if (! isset($nodes[$node['prev_id']])) {
                    throw new \UnexpectedValueException();
                }
                $nodes[$node['prev_id']]['next_id'] = $id;
            } elseif ($node['parent_id']) {
                if (! isset($nodes[$node['parent_id']])) {
                    throw new \UnexpectedValueException();
                }
                $nodes[$node['parent_id']]['child_id'] = $id;
            } elseif ($id) {
                $nodes[0]['child_id'] = $id;
            }
        }

        // 获取正确顺序的 children
        foreach ($nodes as $id => $node) {
            if ($next = $node['child_id']) {
                $children = [];
                while($next) {
                    $children[] = $next;
                    $next = $nodes[$next]['next_id'];
                }
                $nodes[$id]['children'] = $children;
            }
        }

        if (error_get_last()) {
            throw new \UnexpectedValueException();
        }

        return $nodes;
    }

    /**
     * 获取给定节点的父节点
     *
     * @param  int $id
     * @return string|int|null
     */
    public function parent($id)
    {
        if ($node = $this->nodes[$id] ?? null) {
            return $node['parent_id'];
        }
        return null;
    }

    /**
     * 获取给定节点的祖先节点
     *
     * @param int|string $id
     * @return array
     */
    public function ancestors($id)
    {
        if ($node = $this->nodes[$id] ?? null) {
            return $node['path'];
        }
        return [];
    }

    /**
     * 获取给定节点的直接子节点
     *
     * @param int|string $id
     * @return array
     */
    public function children($id)
    {
        if ($node = $this->nodes[$id] ?? null) {
            return $node['children'];
        }
        return [];
    }

    /**
     * 获取给定节点的子孙节点
     *
     * @param int $id
     * @return array
     */
    public function descendants($id)
    {
        $descendants = [];
        foreach ($this->nodes as $id => $node) {
            if (in_array($id, $node['path'])) {
                $descendants[] = $id;
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
        if ($parent = $this->parent($id)) {
            return array_diff($this->nodes[$parent]['children'], [$id]);
        }
        return [];
    }

    /**
     * 获取给定节点的前一个节点
     *
     * @param int $id
     * @return int|null
     */
    public function prev($id)
    {
        if ($node = $this->nodes[$id] ?? null) {
            return $node['prev_id'];
        }
        return null;
    }

    /**
     * 获取给定节点的后一个节点
     *
     * @param int $id
     * @return int|null
     */
    public function next($id)
    {
        if ($node = $this->nodes[$id]) {
            return $node['next_id'];
        }
        return null;
    }
}
