<?php

namespace App\Support;

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

        $this->init();
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function getNodes()
    {
        return $this->nodes;
    }

    public function getNode(array $node = [])
    {
        return array_merge([
            'id' => 0,
            'parent_id' => null,
            'prev_id' => null,
            'next_id' => null,
            'child_id' => null,
            'children' => [],
            'path' => [],
        ], $node);
    }

    /**
     * 初始化：从位置数据生成树节点
     *
     * @return $this
     */
    public function init()
    {
        $this->nodes = [];
        foreach ($this->positions as $position) {
            $id = key([trim($position[$this->keymap['id']]) => null]);
            if ($id !== 0) {
                $this->nodes[$id] = $this->getNode([
                    'id' => $id,
                    'parent_id' => $position[$this->keymap['parent_id']] ?? 0,
                    'prev_id' => $position[$this->keymap['prev_id']] ?? null,
                ]);
            }
        }
        $this->nodes[0] = $this->getNode();

        if (count($this->nodes) === 1) {
            return $this;
        }

        if (count($this->nodes) === 2) {
            $id = key($this->nodes);
            $this->nodes = [
                0 => $this->getNode(['child_id'=>$id, 'children'=>[$id]]),
                $id => $this->getNode(['id'=>$id, 'parent_id'=>0, 'path'=>[0]]),
            ];

            return $this;
        }

        // 校正节点相对位置
        $this->correctRelativePosition();

        // 排序
        $this->sort();

        return $this;
    }

    /**
     * 校正节点相对位置
     *
     * @return void
     */
    protected function correctRelativePosition()
    {
        $this->correctParents();
        $this->correctSiblings();
    }

    /**
     * 校正层级关系
     *
     * @return void
     */
    protected function correctParents()
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

        $parents = [0 => true];
        while (true) {
            $count = 0;
            $children = [];
            foreach ($nodes as $id => $node) {
                $parent_id = $node['parent_id'];
                if ($parents[$parent_id] ?? false) {
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
                $orders[$node['id']] = $node;
                $orders[0]['children'][] = $node['id'];
                $children[$node['id']] = true;
            }
            if (empty($nodes)) {
                break;
            }
            $parents = $children;
        }

        $this->nodes = $orders;
    }

    /**
     * 校正同级关系
     *
     * @return void
     */
    protected function correctSiblings()
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
            $children = $this->sortSiblings($node['children']);
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
     * 校正同级节点顺序
     *
     * @param  array $siblings
     * @return array
     */
    protected function sortSiblings(array $siblings)
    {
        if (count($siblings) <= 1) {
            return $siblings;
        }

        $orders = [];
        $prev = null;
        while (true) {
            $count = 0;
            foreach ($siblings as $id) {
                if ($this->nodes[$id]['prev_id'] === $prev) {
                    $orders[] = $id;
                    $prev = $id;
                    $count++;
                }
            }
            if ($count > 0) {
                $siblings = array_diff($siblings, $orders);
            } else {
                $prev = array_shift($siblings);
                $orders[] = $prev;
            }
            if (empty($siblings)) {
                break;
            }
        }
        return $orders;
    }

    /**
     * 排序节点
     *
     * @return void
     */
    protected function sort()
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
        if (! is_null($parent = $this->parent($id))) {
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
