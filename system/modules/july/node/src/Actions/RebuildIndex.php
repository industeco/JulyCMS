<?php

namespace July\Node\Actions;

use App\Http\Actions\ActionBase;
use Illuminate\Http\Request;
use July\Node\NodeIndex;

/**
 * 重建索引
 *
 * @return \Illuminate\Http\Response
 */
class RebuildIndex extends ActionBase
{
    protected static $routeName = 'rebuild-index';

    protected static $title = '重建索引';

    public function __invoke(Request $request)
    {
        return NodeIndex::rebuild();
    }
}
