<?php

namespace App\ModelIsomers;

use Illuminate\Support\Collection;
use App\Models\Catalog;

class CatalogTree
{
    /**
     * @var \App\Models\Catalog
     */
    protected $catalog = null;

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $nodes = null;

    function __construct(Catalog $catalog)
    {
        $this->catalog = $catalog;

        $cacheid = $catalog->id.'/treeNodes';
        if ($treeNodes = $catalog->cacheGet($cacheid)) {
            $treeNodes = $treeNodes['value'];
        }else {
            $treeNodes = $this->getTreeNodes();
            $catalog->cachePut($cacheid, $treeNodes);
        }
        $this->nodes = collect($treeNodes);
    }

    protected function getTreeNodes()
    {
        $nodes = $this->catalog->retrieveCatalogNodes();

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
        if ($node = $this->nodes->get($id)) {
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
        if ($node = $this->nodes->get($id)) {
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
        if ($this->nodes->has($id)) {
            foreach ($this->nodes as $node_id => $node) {
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
            return $this->nodes->keys()->filter()->all();
        }

        $descendants = [];
        if ($this->nodes->has($id)) {
            $end = false;
            foreach ($this->nodes as $node_id => $node) {
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
        if ($id > 0 && ($node = $this->nodes->get($id))) {
            $parent_id = $node['parent_id'];
            foreach ($this->nodes as $node_id => $node) {
                if ($node['parent_id'] == $parent_id && $node_id != $id) {
                    $siblings[] = $node_id;
                }
            }
        }

        return $siblings;
    }

    // /**
    //  * 获取给定节点的父节点
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function parents(array $ids)
    // {
    //     // $anchors = $this->nodes->only($ids);
    //     // return $this->nodes->only($anchors->pluck('parent_id')->filter());

    //     $parents = [];
    //     foreach ($ids as $id) {
    //         if ($node = $this->nodes->get($id)) {
    //             if ($parent = $this->nodes->get($node['parent_id'])) {
    //                 $parents[$parent['node_id']] = $parent;
    //             }
    //         }
    //     }

    //     return collect($parents);
    // }

    // /**
    //  * 获取给定节点的祖先节点
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function ancestors(array $ids)
    // {
    //     // $anchors = $this->nodes->only($ids);
    //     // return $this->nodes->only($anchors->pluck('path')->flatten());

    //     $ancestors = collect();
    //     foreach ($ids as $id) {
    //         if ($node = $this->nodes->get($id)) {
    //             $ancestors = $ancestors->merge($this->nodes->only($node['path']));
    //         }
    //     }
    //     return $ancestors;
    // }

    // /**
    //  * 获取给定节点的直接子节点
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function children(array $ids)
    // {
    //     // return $this->nodes->filter(function($node) use ($ids) {
    //     //     return $node['parent_id'] && in_array($node['parent_id'], $ids);
    //     // });

    //     $children = [];
    //     foreach ($ids as $id) {
    //         foreach ($this->nodes as $node_id => $node) {
    //             if ($node_id && $node['parent_id'] == $id) {
    //                 $children[$node['node_id']] = $node;
    //             }
    //         }
    //     }

    //     return collect($children);
    // }

    // /**
    //  * 获取给定节点的子孙节点
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function descendants(array $ids)
    // {
    //     // $parents = collect($ids);
    //     // return $this->nodes->filter(function($node) use ($parents) {
    //     //     return ! $parents->intersect($node->parents)->isEmpty();
    //     // });

    //     if (in_array(0, $ids)) {
    //         return $this->nodes;
    //     }

    //     $descendants = [];
    //     foreach ($ids as $id) {
    //         if (isset($descendants[$id])) {
    //             continue;
    //         }
    //         $end = false;
    //         foreach ($this->nodes as $node_id => $node) {
    //             if ($end) {
    //                 if ($node_id == $end) {
    //                     break;
    //                 } else {
    //                     $descendants[$node_id] = $node;
    //                 }
    //             }
    //             if ($node_id == $id) {
    //                 $end = $node['next_id'] ?? -1;
    //                 continue;
    //             }
    //         }
    //     }

    //     return collect($descendants);
    // }

    // /**
    //  * 获取给定节点的同级节点，不包含自身
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function siblings(array $ids)
    // {
    //     $anchors = $this->nodes->only($ids);
    //     $siblings = collect();
    //     foreach ($anchors as $anchor) {
    //         if ($parent = $this->nodes->get($anchor['parent'])) {
    //             $siblings = $siblings->merge($parent['children']);
    //         }
    //     }
    //     return $this->nodes->only($siblings->diff($ids));
    // }

    // /**
    //  * 获取指定节点所在路径（节点 id 的集合）
    //  *
    //  * @param array $ids
    //  * @return TreePath
    //  */
    // public function path(array $ids)
    // {
    //     $parents = $this->nodes->only($ids)->pluck('parents')->flatten()->filter();
    //     return TreePath::make($parents->merge($ids)->all());
    // }
}
