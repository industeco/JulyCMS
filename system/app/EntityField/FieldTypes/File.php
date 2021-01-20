<?php

namespace App\EntityField\FieldTypes;

class File extends FieldTypeBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'file';

    /**
     * 字段类型标签
     *
     * @var string
     */
    protected $label = '文件名';

    /**
     * 字段类型描述
     *
     * @var string|null
     */
    protected $description = '可输入带路径文件名，输入框右侧带文件浏览按钮';

    /**
     * {@inheritdoc}
     */
    public function extractParameters(array $raw)
    {
        $parameters = parent::extractParameters($raw);

        if ($parameters['helptext'] ?? null) {
            return $parameters;
        }

        if ($fileBundle = $parameters['file_bundle'] ?? null) {
            if ($exts = config('jc.validation.file_bundles.'.$fileBundle)) {
                $parameters['helptext'] = '允许的扩展名：'.join(', ', $exts);
            }
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns(?string $fieldName = null, ?array $parameters = [])
    {
        $fieldName = $fieldName ?? $this->field->getKey();
        $column = [
            'type' => 'string',
            'name' => $fieldName.'_value',
            'parameters' => [
                'length' => 200,
            ],
        ];

        return [$column];
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(?array $parameters = [])
    {
        $parameters = $parameters ?: $this->field->getParameters();

        $rules = parent::getRules($parameters);

        if ($fileBundle = $parameters['file_bundle'] ?? null) {
            if ($exts = config('jc.validation.file_bundles.'.$fileBundle)) {
                $exts = join('|', $exts);
                $rules[] = "{pattern: /^(\\/[a-z0-9\\-_]+)+\\.($exts)$/, message:'文件格式不正确', trigger:'submit'}";
            }
        }

        return $rules;
    }
}
