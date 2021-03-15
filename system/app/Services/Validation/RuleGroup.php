<?php

namespace App\Services\Validation;

use App\Services\Validation\RuleFormats\FormatBase;
use App\Utils\Makable;
use ArrayIterator;
use IteratorAggregate;

class RuleGroup implements IteratorAggregate
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

    public function __construct(string $raw, ?string $field = null)
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
     * 获取规则名
     *
     * @return string
     */
    public function hasRule(string $name)
    {
        return isset($this->rules[$name]);
    }

    /**
     * 解析规则字符串
     *
     * @param  string $raw
     * @return array
     */
    public function explode(string $raw)
    {
        if (false === strpos($raw, '\\')) {
            return explode('|', $raw);
        }

        $raw = str_replace('\\|', '{LINE}', str_replace('\\\\', '{SLASH}', trim($raw)));
        return str_replace(['{SLASH}', '{LINE}'], ['\\\\', '|'], explode('|', $raw));
    }

    /**
     * 将规则整体转换为指定格式
     *
     * @param  \App\Services\Validation\RuleFormats\FormatBase $format
     * @return mixed
     */
    public function parseTo(FormatBase $format)
    {
        return $format->parseGroup($this);
    }

    /**
     * Get an iterator for the rules.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->rules);
    }
}
