<?php

namespace App\EntityField\FieldTypeDefinitions;

class Any extends DefinitionBase
{
    protected $attributes = [
        'id' => 'any',
        'label' => '空类型',
        'description' => '不预设任何参数，需要在模板中具体设置',
        'schema' => [],
    ];

    /**
     * {@inheritdoc}
     */
    public function getParametersSchema(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function extractParameters(array $raw): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = []): array
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
