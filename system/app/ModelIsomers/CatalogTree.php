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
    protected $contents = null;

    function __construct(Catalog $catalog)
    {
        $this->catalog = $catalog;

        $cachekey = $catalog->cacheKey('treeNodes');
        if ($treeNodes = $catalog->cacheGet($cachekey)) {
            $treeNodes = $treeNodes['value'];
        }else {
            $treeNodes = $this->getTreeNodes();
            $catalog->cachePut($cachekey, $treeNodes);
        }
        $this->contents = collect($treeNodes);
    }

    protected function getTreeNodes()
    {
        $contents = $this->catalog->cacheGetCatalogContents();

        $treeNodes = [
            0 => [
                'content_id' => 0,
                'parent_id' => null,
                'prev_id' => null,
                'path' => [],
            ],
        ];

        if (empty($contents)) {
            return $treeNodes;
        }

        if (count($contents) === 1) {
            $content = reset($contents);
            $content['path'] = array_values(array_filter(explode('/', $content['path'])));
            $treeNodes[$content['content_id']] = $content;

            return $treeNodes;
        }

        // $contents = $this->sortNodes($contents);
        foreach ($this->sortNodes($contents) as $id => $content) {
            $content['parent_id'] = $content['parent_id'] ?? 0;
            $treeNodes[$id] = $content;
        }

        return $treeNodes;
    }

    protected function sortNodes(array $contents)
    {
        $contents = collect($contents)->map(function($content) {
            $path = array_values(array_filter(explode('/', $content['path'])));
            return [
                'content_id' => (int) $content['content_id'],
                'parent_id' => (int) $content['parent_id'],
                'prev_id' => (int) $content['prev_id'],
                'path' => array_map('intval', $path),
            ];
        })->keyBy('content_id')->toArray();

        $first = null;
        foreach ($contents as $id => $content) {
            if ($content['prev_id']) {
                $contents[$content['prev_id']]['next_id'] = $id;
            } elseif ($content['parent_id']) {
                $contents[$content['parent_id']]['child_id'] = $id;
            } elseif (!$first) {
                $first = $id;
            }
        }

        $sortedNodes = [];
        $content = $contents[$first];
        while (true) {
            if (! isset($sortedNodes[$content['content_id']])) {
                $sortedNodes[$content['content_id']] = $content;
                if ($content['child_id'] ?? null) {
                    $content = $contents[$content['child_id']];
                    continue;
                }
            }

            if ($content['next_id'] ?? null) {
                $content = $contents[$content['next_id']];
            } elseif ($content['parent_id']) {
                $content = $contents[$content['parent_id']];
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
    public function contents(array $ids = null)
    {
        if ($ids) {
            return $this->contents->only($ids)->keys()->filter()->all();
        }
        return $this->contents->keys()->filter()->all();
    }

    /**
     * 获取给定节点的父节点
     *
     * @param int $id
     * @return array
     */
    public function parent($id)
    {
        if ($content = $this->contents->get($id)) {
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
        if ($content = $this->contents->get($id)) {
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
        if ($this->contents->has($id)) {
            foreach ($this->contents as $content_id => $content) {
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
            return $this->contents->keys()->filter()->all();
        }

        $descendants = [];
        if ($this->contents->has($id)) {
            $end = false;
            foreach ($this->contents as $content_id => $content) {
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
        if ($id > 0 && ($content = $this->contents->get($id))) {
            $parent_id = $content['parent_id'];
            foreach ($this->contents as $content_id => $content) {
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
        if ($content = $this->contents->get($id)) {
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
        if ($content = $this->contents->get($id)) {
            return $content['next_id'] ?? null;
        }
        return null;
    }

    // /**
    //  * 获取给定节点的父节点
    //  *
    //  * @param array $ids
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function parents(array $ids)
    // {
    //     // $anchors = $this->contents->only($ids);
    //     // return $this->contents->only($anchors->pluck('parent_id')->filter());

    //     $parents = [];
    //     foreach ($ids as $id) {
    //         if ($content = $this->contents->get($id)) {
    //             if ($parent = $this->contents->get($content['parent_id'])) {
    //                 $parents[$parent['content_id']] = $parent;
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
    //     // $anchors = $this->contents->only($ids);
    //     // return $this->contents->only($anchors->pluck('path')->flatten());

    //     $ancestors = collect();
    //     foreach ($ids as $id) {
    //         if ($content = $this->contents->get($id)) {
    //             $ancestors = $ancestors->merge($this->contents->only($content['path']));
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
    //     // return $this->contents->filter(function($content) use ($ids) {
    //     //     return $content['parent_id'] && in_array($content['parent_id'], $ids);
    //     // });

    //     $children = [];
    //     foreach ($ids as $id) {
    //         foreach ($this->contents as $content_id => $content) {
    //             if ($content_id && $content['parent_id'] == $id) {
    //                 $children[$content['content_id']] = $content;
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
    //     // return $this->contents->filter(function($content) use ($parents) {
    //     //     return ! $parents->intersect($content->parents)->isEmpty();
    //     // });

    //     if (in_array(0, $ids)) {
    //         return $this->contents;
    //     }

    //     $descendants = [];
    //     foreach ($ids as $id) {
    //         if (isset($descendants[$id])) {
    //             continue;
    //         }
    //         $end = false;
    //         foreach ($this->contents as $content_id => $content) {
    //             if ($end) {
    //                 if ($content_id == $end) {
    //                     break;
    //                 } else {
    //                     $descendants[$content_id] = $content;
    //                 }
    //             }
    //             if ($content_id == $id) {
    //                 $end = $content['next_id'] ?? -1;
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
    //     $anchors = $this->contents->only($ids);
    //     $siblings = collect();
    //     foreach ($anchors as $anchor) {
    //         if ($parent = $this->contents->get($anchor['parent'])) {
    //             $siblings = $siblings->merge($parent['children']);
    //         }
    //     }
    //     return $this->contents->only($siblings->diff($ids));
    // }

    // /**
    //  * 获取指定节点所在路径（节点 id 的集合）
    //  *
    //  * @param array $ids
    //  * @return TreePath
    //  */
    // public function path(array $ids)
    // {
    //     $parents = $this->contents->only($ids)->pluck('parents')->flatten()->filter();
    //     return TreePath::make($parents->merge($ids)->all());
    // }
}
