<?php

namespace July\Message;

use App\EntityField\FieldBase;
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
     * 解析字段验证规则和定制的错误信息
     *
     * @return array
     */
    public function resolveRules()
    {
        if (empty($entries = $this->rules)) {
            return [[], []];
        }

        $rules = [];
        $messages = [];
        $key = $this->getKey();
        $required = false;
        foreach (explode('|', $entries) as $entry) {
            $details = $this->resolveRuleEntry(trim($entry));
            if ($details['name'] === 'required') {
                $required = true;
            }
            $rules[] = $details['rule'];
            if (! is_null($details['message'])) {
                $messages[$key.'.'.$details['name']] = $details['message'];
            }
        }
        if (!$required && $this->is_required) {
            $rules[] = 'required';
        }

        return [[$key => $rules], $messages];
    }

    protected function resolveRuleEntry(string $entry)
    {
        $entry = explode('=>', $entry);
        return [
            'rule' => $entry[0],
            'name' => explode(':', $entry[0])[0],
            'message' => $rule[1] ?? null,
        ];
    }
}
