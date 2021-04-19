<?php

namespace App\Support\Validation\RuleFormats;

use App\Support\Validation\Rule;

class JsRule extends FormatBase
{
    /**
     * 默认的消息模板
     *
     * @var array
     */
    protected static $messageTemplates = [
        'required' => '不能为空',
        'max' => '最多 {max} 个字符',
        'email' => '邮件格式不正确',
        'url' => '网址格式不正确',
        'pattern' => '格式不正确',
        'pathAlias' => '网址格式不正确',
        'exists' => '已存在',
    ];

    /**
     * 默认转换规则
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function parseDefault(Rule $rule)
    {
        $message = $rule->resolveMessage() ?? static::$messageTemplates[$rule->getName()] ?? '';

        return "{type:'{$rule->getName()}',message:'{$message}',trigger:'blur'}";
    }

    /**
     * required
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function required(Rule $rule)
    {
        $message = $rule->resolveMessage() ?? '不能为空';

        return "{required:true,message:'{$message}',trigger:'submit'}";
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
        $message = $rule->resolveMessage(compact('max')) ?? "最多 {$max} 个字符";

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
        $message = $rule->resolveMessage() ?? '格式不正确';

        return "{pattern:{$pattern},message:'{$message}',trigger:'blur'}";
    }

    /**
     * path-alias
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function pathAlias(Rule $rule)
    {
        $message = $rule->resolveMessage() ?? '网址格式不正确';

        return "{pattern:/^(\\/[a-z0-9\\-_]+)+(\\.html)?$/,message:'{$message}',trigger:'blur'}";
    }

    /**
     * pattern
     *
     * @param  \App\Support\Validation\Rule $rule
     * @return string
     */
    protected function exists(Rule $rule)
    {
        $params = explode(',', $rule->getParameters(), 3);
        $route = htmlspecialchars(short_url($params[0]), ENT_QUOTES|ENT_HTML5);
        $current = htmlspecialchars($params[1] ?? '', ENT_QUOTES|ENT_HTML5);
        $except = $params[2] ?? '';

        return "{validator:exists('{$route}','{$current}',{$except}),trigger:'blur'}";
    }
}
