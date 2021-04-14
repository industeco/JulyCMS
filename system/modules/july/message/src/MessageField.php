<?php

namespace July\Message;

use App\EntityField\FieldBase;
use App\Support\Validation\RuleFormats\LaravelRule;
use Illuminate\Support\Facades\Log;

class MessageField extends FieldBase
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'message_fields';

    /**
     * 获取实体类
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return Message::class;
    }

    /**
     * 获取翻译类
     *
     * @return string
     */
    public static function getTranslationClass()
    {
        return MessageFieldTranslation::class;
    }

    /**
     * 解析字段验证规则和定制的错误信息
     *
     * @return array
     */
    public function resolveRules()
    {
        return $this->getFieldType()->getRules()->parseTo(new LaravelRule);
    }
}
