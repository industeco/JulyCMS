<?php

namespace App\Contracts;

use App\ModelCollections\NodeCollection;

interface GetNodes
{
    /**
     * @return \App\ModelCollections\NodeCollection
     */
    public function get_nodes():NodeCollection;
}
