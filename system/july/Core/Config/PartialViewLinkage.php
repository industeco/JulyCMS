<?php

namespace July\Core\Config;

use App\Utils\Pattern;
use July\Core\Entity\Linkage\LinkageBase;

class PartialViewLinkage extends LinkageBase
{
    /**
     * {@inheritdoc}
     */
    protected function performGet()
    {
        return $this->entity->getPartialView();
    }

    /**
     * {@inheritdoc}
     */
    protected function performSet($value)
    {
        if (is_null($value)) {
            return $this->performDelete();
        }

        if (! Pattern::isTwig($value)) {
            throw new \TypeError('URL 格式不正确');
        }

        PartialView::query()->updateOrCreate([
            'path' => $this->entity->getEntityPath(),
            'langcode' => $this->entity->getLangcode(),
        ], ['view' => $value]);
    }

    /**
     * {@inheritdoc}
     */
    protected function performDelete()
    {
        PartialView::query()->where([
            'path' => $this->entity->getEntityPath(),
            'langcode' => $this->entity->getLangcode(),
        ])->delete();
    }

    /**
     * {@inheritdoc}
     */
    protected function performSearch(string $needle)
    {
        return [];
    }
}
