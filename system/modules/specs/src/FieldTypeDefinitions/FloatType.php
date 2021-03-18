<?php

namespace Specs\FieldTypeDefinitions;

use App\Support\Types;

class FloatType extends DefinitionBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'float';

    /**
     * 字段类型标题
     *
     * @var string
     */
    protected $label = '小数';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $type = 'double';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $parameters = [
        'nullable' => true,
        'total' => null,
        'places' => null,
        'unsigned' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array_merge(parent::getParameters(), [
            'places' => $this->field['places'] ?? null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function cast($value)
    {
        $places = $this->getParameters()['places'] ?? null;
        return Types::cast($value, $places ? 'decimal:'.$places : 'double');
    }
}
