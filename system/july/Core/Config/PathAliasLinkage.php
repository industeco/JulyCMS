<?php

namespace July\Core\Config;

use App\Utils\Pattern;
use July\Core\Entity\Linkage\LinkageBase;

class PathAliasLinkage extends LinkageBase
{
    /**
     * {@inheritdoc}
     */
    protected function performGet()
    {
        return $this->entity->getPathAlias();
    }

    /**
     * {@inheritdoc}
     */
    protected function performSet($value)
    {
        if (is_null($value)) {
            return $this->performDelete();
        }

        if (!Pattern::isUrl($value)) {
            throw new \TypeError('URL 格式不正确');
        }

        PathAlias::query()->updateOrCreate([
            'path' => $this->entity->getEntityPath(),
            'langcode' => $this->entity->getLangcode(),
        ], ['alias' => $value]);
    }

    /**
     * {@inheritdoc}
     */
    protected function performDelete()
    {
        PathAlias::query()->where([
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
