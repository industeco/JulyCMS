<?php

namespace App\Services\Validation\RuleFormats;

use App\Services\Validation\Rule;

class JsRule extends FormatBase
{
    /**
     * 默认转换规则
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return string
     */
    protected function parseDefault(Rule $rule)
    {
        return "{type:'{$rule->getName()}',message:'{$rule->resolveMessage()}',trigger:'blur'}";
    }

    /**
     * required
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return string
     */
    protected function required(Rule $rule)
    {
        return "{required:true,message:'{$rule->resolveMessage()}',trigger:'submit'}";
    }

    /**
     * max
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return string
     */
    protected function max(Rule $rule)
    {
        $max = (int) $rule->getParameters();
        $message = $rule->resolveMessage(compact('max'));

        return "{max:{$max},message:'{$message}',trigger:'change'}";
    }

    /**
     * pattern
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return string
     */
    protected function pattern(Rule $rule)
    {
        $pattern = trim($rule->getParameters());

        return "{pattern:{$pattern},message:'{$rule->resolveMessage()}',trigger:'blur'}";
    }
}
