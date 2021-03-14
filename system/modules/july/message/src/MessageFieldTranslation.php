<?php

namespace July\Message;

use App\EntityField\FieldTranslationBase;

class MessageFieldTranslation extends FieldTranslationBase
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'message_field_translations';

    /**
     * 获取绑定的字段类
     *
     * @return string
     */
    public function getFieldClass()
    {
        return MessageField::class;
    }
}
