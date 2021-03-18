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

        return compact('rules', 'messages');
    }

    /**
     * 默认转换规则
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return array
     */
    protected function parseDefault(Rule $rule)
    {
        return [
            $rule->getName(),
            [$rule->getMessageKey() => $rule->resolveMessage()],
        ];
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

        return ['max:'.$max, [$rule->getMessageKey() => $rule->resolveMessage(compact('max'))]];
    }

    /**
     * pattern
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return array
     */
    protected function pattern(Rule $rule)
    {
        $pattern = trim($this->parameters);

        return ['regex:'.$pattern, [$rule->getMessageKey() => $rule->resolveMessage()]];
    }

    /**
     * path-alias
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function pathAlias(Rule $rule)
    {
        return ['regex:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/', [$rule->getMessageKey() => $rule->resolveMessage()]];
    }
}
