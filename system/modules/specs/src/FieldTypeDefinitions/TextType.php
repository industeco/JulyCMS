<?php

namespace Specs\FieldTypeDefinitions;

class TextType extends DefinitionBase
{
    /**
     * 字段类型 id
     *
     * @var string
     */
    protected $id = 'text';

    /**
     * 字段类型标题
     *
     * @var string
     */
    protected $label = '文本';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $type = 'string';

    /**
     * 数据库列类型
     *
     * @var string
     */
    protected $parameters = [
        'length' => 255,
        'nullable' => true,
    ];
}
