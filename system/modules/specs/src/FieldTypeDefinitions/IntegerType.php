<?php

namespace Specs\FieldTypeDefinitions;

use App\Support\Types;

class IntegerType extends DefinitionBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'integer';

    /**
     * 字段类型标题
     *
     * @var string
     */
    protected $label = '整数';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $type = 'bigInteger';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $parameters = [
        'nullable' => true,
        'autoIncrement' => false,
        'unsigned' => false,
    ];

    /**
     * {@inheritdoc}
     */
    public function cast($value)
    {
        return Types::cast($value, 'integer');
    }
}
