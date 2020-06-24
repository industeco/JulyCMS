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

    public function get_contents(): ContentCollection
    {
        $tags = $this->pluck('tag')->all();
        $contents = DB::table('content_tag')
            ->select(DB::raw('content_id, count(content_id) as content_count'))
            ->whereIn('tag', $tags)
            ->groupBy('content_id')
            ->having('content_count', '>=', $this->matches)
            ->get()
            ->pluck('content_id')->all();

        return ContentCollection::find($contents);
    }
}
