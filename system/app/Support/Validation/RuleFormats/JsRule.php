<?php

namespace App\Support\Validation\RuleFormats;

use App\Support\Validation\Rule;

class JsRule extends FormatBase
{
    /**
     * 默认转换规则
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function parseDefault(Rule $rule)
    {
        return "{type:'{$rule->getName()}',message:'{$rule->resolveMessage()}',trigger:'blur'}";
    }

    /**
     * required
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function required(Rule $rule)
    {
        return "{required:true,message:'{$rule->resolveMessage()}',trigger:'submit'}";
    }

    /**
     * max
     *
     * @param  \App\Support\Validation\Rule $rule
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
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function pattern(Rule $rule)
    {
        $pattern = trim($rule->getParameters());

        return "{pattern:{$pattern},message:'{$rule->resolveMessage()}',trigger:'blur'}";
    }

    /**
     * path-alias
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function pathAlias(Rule $rule)
    {
        return "{pattern:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/,message:'{$rule->resolveMessage()}',trigger:'blur'}";
    }

    /**
     * pattern
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function exists(Rule $rule)
    {
        $params = explode(',', $rule->getParameters(), 2);
        $route = htmlspecialchars(short_url($params[0]), ENT_QUOTES|ENT_HTML5);
        $except = htmlspecialchars($params[1] ?? '', ENT_QUOTES|ENT_HTML5);

        return "{validator:exists('{$route}','{$except}'),trigger:'blur'}";
    }
}
