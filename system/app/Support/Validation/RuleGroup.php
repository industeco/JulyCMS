<?php

namespace App\Support\Validation;

use App\Support\Validation\RuleFormats\FormatBase;
use App\Support\Makable;
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
    protected $key;

    /**
     * 生成的规则列表
     *
     * @var \App\Support\Validation\Rule[]
     */
    protected $rules;

    public function __construct(string $raw, ?string $key = null)
    {
        $this->raw = [];
        $this->key = $key;

        $this->rules = [];

        $this->addRules($raw);
    }

    /**
     * 设置规则所要验证的对象
     *
     * @param  string $key
     * @return $this
     */
    public function setKey(string $key)
    {
        $this->key = $key;
        foreach ($this->rules as $rule) {
            $rule->setKey($key);
        }

        return $this;
    }

    /**
     * 获取规则所要验证的对象
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
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
            $rule = Rule::make($rule, $this->key);
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
     * @param  \App\Support\Validation\RuleFormats\FormatBase $format
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
