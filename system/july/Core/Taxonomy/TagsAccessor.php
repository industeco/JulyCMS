<?php

namespace July\Core\Taxonomy;

use July\Core\Entity\Exceptions\InvalidEntityException;
use July\Core\EntityField\Accessor\FieldAccessorBase;

class TagsAccessor extends FieldAccessorBase
{
    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function set($tags)
    {
        if (!$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        if (is_null($tags)) {
            $this->delete();
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
    public function delete()
    {
        if (!$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $needle)
    {
        return [];
    }
}
