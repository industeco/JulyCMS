<?php

namespace App\Services\Validation;

use App\Utils\Makable;

class Rules
{
    use Makable;

    /**
     * 原始的规则字符串
     *
     * @var string[]
     */
    protected $raw;

    /**
     * 字段名
     *
     * @var string|null
     */
    protected $field;

    /**
     * 生成的规则列表
     *
     * @var \App\Services\Validation\Rule[]
     */
    protected $rules;

    public function __construct(string $raw, string $field)
    {
        $this->raw = [];
        $this->field = $field;

        $this->rules = [];

        $this->addRules($raw);
    }

    /**
     * 设置规则所属字段
     *
     * @param  string $field
     * @return $this
     */
    public function setField(string $field)
    {
        $this->field = $field;
        foreach ($this->rules as $rule) {
            $rule->setField($field);
        }

        return $this;
    }

    /**
     * 从字符串表示中解析规则
     *
     * @param  string $raw
     * @return $this
     */
    public function addRules(string $raw)
    {
        $this->raw[] = $raw;

        foreach ($this->explode($raw) as $rule) {
            $rule = Rule::make($rule, $this->field);
            if ($name = $rule->getName()) {
                $this->rules[$name] = $rule;
            }
        }

        return $this;
    }

    /**
     * 获取规则
     *
     * @return array
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * 解析规则字符串
     *
     * @param  string $raw
     * @return array
     */
    public function explode(string $raw)
    {
        $raw = str_replace('\\|', '{#LINE#}', str_replace('\\\\', '{#SLASH#}', trim($raw)));

        return array_map(function($rule) {
            return str_replace(['{#SLASH#}', '{#LINE#}'], ['\\', '|'], $rule);
        }, explode('|', $raw));
    }

    /**
     * 整体转换为 Laravel 规则
     *
     * @return array
     */
    public function toLaravelRules()
    {
        $rules = [];
        $messages = [];
        foreach ($this->rules as $rule) {
            if ($rule = $rule->toLaravelRule()) {
                $rules[] = $rule[0];
                $messages = array_merge($messages, $rule[1]);
            }
        }

        return compact('rules', 'messages');
    }

    /**
     * 整体转换为 js 规则
     *
     * @return array
     */
    public function toJsRules()
    {
        $rules = [];
        foreach ($this->rules as $rule) {
            if ($rule = $rule->toJsRule()) {
                $rules[] = $rule;
            }
        }

        return $rules;
    }
}
