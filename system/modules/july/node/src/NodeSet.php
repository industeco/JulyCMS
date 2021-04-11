<?php

namespace July\Node;

use App\Models\ModelSetBase;
use App\Support\Arr;

class NodeSet extends ModelSetBase
{
    /**
     * 获取绑定的模型
     *
     * @return string
     */
    public static function getModelClass()
    {
        return Node::class;
    }

    public static function isTranslatable()
    {
        return true;
    }

    /**
     * 在指定的树中，获取当前节点集的直接子节点
     *
     * @param  mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_children($catalog = null)
    {
        $ids = $this->pluck('id')->all();

        return CatalogSet::fetch($catalog)->get_children(...$ids);
    }

    public function get_under($catalog = null)
    {
        return $this->get_children($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的所有子节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_descendants($catalog = null)
    {
        $ids = array_keys($this->items);

        return CatalogSet::fetch($catalog)->get_descendants(...$ids);
    }

    public function get_below($catalog = null)
    {
        return $this->get_descendants($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的直接父节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_parent($catalog = null)
    {
        $ids = $this->pluck('id')->all();

        return CatalogSet::fetch($catalog)->get_parent(...$ids);
    }

    public function get_over($catalog = null)
    {
        return $this->get_parent($catalog);
    }

    /**
     * 在指定的树中，获取当前节点集的所有上级节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_ancestors($catalog = null)
    {
        $ids = $this->pluck('id')->all();

        return CatalogSet::fetch($catalog)->get_ancestors(...$ids);
    }

    public function get_above($catalog = null)
    {
        return $this->get_ancestors($catalog);
    }

    /**
     * 在指定的树中，获取当前节点的相邻节点
     *
     * @param mixed $catalog
     * @return \July\Node\NodeSet
     */
    public function get_siblings($catalog = null)
    {
        $ids = $this->pluck('id')->all();

        return CatalogSet::fetch($catalog)->get_siblings(...$ids);
    }

    public function get_around($catalog = null)
    {
        return $this->get_siblings($catalog);
    }

    public function get_molds()
    {
        $molds = $this->pluck('mold_id')->unique()->all();

        return NodeTypeSet::fetch($molds);
    }

    // public function get_tags()
    // {
    //     $ids = $this->pluck('id')->unique()->all();
    //     $langcode = config('render_langcode') ?? langcode('frontend');

    //     $tags = DB::table('node_tag')
    //         ->whereIn('node_id', $ids)
    //         ->where('langcode', $langcode)
    //         ->get('tag')->pluck('tag')->all();

    //     return TagSet::find($tags);
    // }

    // public function match_tags(array $tags, $matches = null)
    // {
    //     $contents = TagSet::find($tags)->match($matches)->get_contents();
    //     return $this->only($contents->pluck('id'));
    // }
}
