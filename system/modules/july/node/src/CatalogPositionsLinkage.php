<?php

namespace July\Node;

use App\Entity\Linkage\LinkageBase;

class CatalogPositionsLinkage extends LinkageBase
{
    protected $default = [];

    /**
     * {@inheritdoc}
     */
    protected function performGet()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function performSet($value)
    {
        if (is_null($value)) {
            $this->performDelete();
            return;
        }

        if (! is_array($value)) {
            throw new \TypeError('$tags 必须是数组');
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function performDelete()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    protected function performSearch(string $needle)
    {
        return [];
    }
}
