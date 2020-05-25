<?php

namespace App\ModelCollections;

use App\Models\NodeTag;
use App\Models\Tag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TagCollection extends ModelCollection
{
    protected static $model = Tag::class;
    protected static $primaryKey = 'tag';

    protected $matches = 1;

    public function match($matches = null)
    {
        switch ($matches) {
            case 'any':
                $this->matches = 1;
                break;
            case 'all':
                $this->matches = $this->count();
                break;

            default:
                $this->matches = intval($matches) ?: 1;
                break;
        }
        return $this;
    }

    public function match_all()
    {
        $this->matches = $this->count();

        return $this;
    }

    public function match_any()
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
