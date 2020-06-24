<?php

namespace App\Contracts;

use App\ModelCollections\ContentCollection;

interface GetContents
{
    /**
     * @return \App\ModelCollections\ContentCollection
     */
    public function get_contents():ContentCollection;
}
