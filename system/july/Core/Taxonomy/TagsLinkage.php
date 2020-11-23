<?php

namespace July\Core\Taxonomy;

use July\Core\Entity\Linkage\LinkageBase;

class TagsLinkage extends LinkageBase
{
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
    protected function performSet($tags)
    {
        if (is_null($tags)) {
            $this->performDelete();
            return;
        }

        if (! is_array($tags)) {
            throw new \TypeError('tags 必须是数组');
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
