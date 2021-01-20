<?php

namespace App\EntityField\FieldTypes;

class PathAlias extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'path_alias';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '网址';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '实体路径别名';

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getMaterials(?array $data = []): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getComponent(?array $data = []): ?string
    {
        $data = $data ?: $this->field->gather();

        if (! isset($data['parameters']['helptext'])) {
            $data['parameters']['helptext'] = $data['description'] ?? null;
        }

        return view('backend::components.'.$this->getKey(), $data)->render();
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(?array $parameters = []): array
    {
        return [];
    }
}
