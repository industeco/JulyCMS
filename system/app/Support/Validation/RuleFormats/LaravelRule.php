<?php

namespace App\Support\Validation\RuleFormats;

use App\Support\Validation\Rule;
use App\Support\Validation\RuleGroup;

class LaravelRule extends FormatBase
{
    /**
     * 转换规则集
     *
     * @param  \App\Support\Validation\RuleGroup $group
     * @return array
     */
    public function parseGroup(RuleGroup $group)
    {
        $rules = [];
        $messages = [];
        foreach ($group as $rule) {
            if ($rule = $this->parse($rule)) {
                $rules[] = $rule[0];
                $messages = array_merge($messages, $rule[1]);
            }
        }

        return [[$group->getKey() => $rules], $messages];
    }

    /**
     * 默认转换规则
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return array
     */
    protected function parseDefault(Rule $rule)
    {
        $msg = [];
        if (!is_null($message = $rule->resolveMessage())) {
            $msg = [$rule->getMessageKey() => $message];
        }

        return [$rule->getRule(), $msg];
    }

    /**
     * max
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return array
     */
    protected function max(Rule $rule)
    {
        $max = (int) $rule->getParameters();
        $msg = [];
        if (!is_null($message = $rule->resolveMessage(compact('max')))) {
            $msg = [$rule->getMessageKey() => $message];
        }

        return ['max:'.$max, $msg];
    }

    /**
     * pattern
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return array
     */
    protected function pattern(Rule $rule)
    {
        $pattern = trim($rule->getParameters());
        $msg = [];
        if (!is_null($message = $rule->resolveMessage())) {
            $msg = [$rule->getMessageKey() => $message];
        }

        return ['regex:'.$pattern, $msg];
    }

    /**
     * path-alias
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function pathAlias(Rule $rule)
    {
        $msg = [];
        if (!is_null($message = $rule->resolveMessage())) {
            $msg = [$rule->getMessageKey() => $message];
        }

        return ['regex:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/', $msg];
    }

    /**
     * path-alias
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function in(Rule $rule)
    {
        $parameters = trim($rule->getParameters());
        $msg = [];
        if (!is_null($message = $rule->resolveMessage())) {
            $msg = [$rule->getMessageKey() => $message];
        }

        return ['in:'.$parameters, $msg];
    }
}
