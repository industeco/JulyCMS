<?php

namespace July\Core\Config;

use July\Core\Entity\Exceptions\InvalidEntityException;
use July\Core\EntityField\Accessor\FieldAccessorBase;

class PathAliasAccessor extends FieldAccessorBase
{
    /**
     * {@inheritdoc}
     */
    public function get()
    {
        if (!$this->entity || !$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        return PathAlias::findAliasByPath($this->entity->getEntityPath())
            ->get($this->entity->getLangcode());
    }

    /**
     * {@inheritdoc}
     */
    public function set($value)
    {
        if (!$this->entity || !$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        if (is_null($value)) {
            $this->delete();
            return;
        }

        if (!$this->isValideUrl($value)) {
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
    public function delete()
    {
        if (!$this->entity || !$this->entity->exists) {
            throw new InvalidEntityException('字段存取器的关联实体无效');
        }

        PathAlias::query()->where([
            'path' => $this->entity->getEntityPath(),
            'langcode' => $this->entity->getLangcode(),
        ])->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $needle)
    {
        return [];
    }

    /**
     * 验证是否合法的 URL
     *
     * @param  mixed $url
     * @return bool
     */
    protected function isValideUrl($url)
    {
        if (!is_string($url) || empty($url)) {
            return false;
        }

        if (preg_match('/^(\/[a-z0-9\-_]+)+(\.html)?$/i', $url)) {
            return true;
        }

        return false;
    }
}
