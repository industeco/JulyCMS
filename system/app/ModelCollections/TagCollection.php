<?php

namespace App\ModelCollections;

use App\Models\NodeTag;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;

class TagCollection extends ModelCollection
{
    protected $matches = 1;

    public static function find($args)
    {
        if (empty($args)) {
            return new static(Tag::fetchAll());
        }

        if (! is_array($args)) {
            $args = [$args];
        }

        $items = [];
        foreach ($args as $arg) {
            if (is_string($arg)) {
                if ($tag = Tag::fetch($arg)) {
                    $items[$tag->tag] = $tag;
                }
            } elseif ($arg instanceof Tag) {
                $items[$arg->tag] = $arg;
            } elseif ($arg instanceof static) {
                $items = array_merge($items, $arg->all());
            }
        }

        return new static($items);
    }

    public function match($matches)
    {
        $this->matches = intval($matches) ?: 1;

        return $this;
    }

    public function matchAll()
    {
        $this->matches = count($this->items);

        return $this;
    }

    public function matchAny()
    {
        $this->matches = 1;

        return $this;
    }

    public function get_nodes(): NodeCollection
    {
        $tags = $this->pluck('tag')->all();
        $nodes = DB::table('node_tag')
            ->select(DB::raw('node_id, count(node_id) as node_count'))
            ->whereIn('tag', $tags)
            ->groupBy('node_id')
            ->having('node_count', '>=', $this->matches)
            ->get()
            ->pluck('node_id')->all();

        return NodeCollection::find($nodes);
    }
}
