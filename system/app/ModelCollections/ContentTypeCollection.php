<?php

namespace App\ModelCollections;

use App\Models\Content;
use App\Models\ContentType;
use Illuminate\Support\Collection;

class ContentTypeCollection extends ModelCollection
{
    protected static $model = ContentType::class;
    protected static $primaryKey = 'truename';

    public function get_contents(): ContentCollection
    {
        $types = $this->pluck('truename')->all();
        $contents = Content::whereIn('content_type', $types)->get('id')->pluck('id')->all();
        return ContentCollection::find($contents);
    }
}
