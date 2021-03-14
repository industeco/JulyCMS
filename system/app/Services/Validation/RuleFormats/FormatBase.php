<?php

namespace App\Services\Validation\RuleFormats;

use App\Services\Validation\Rule;
use App\Services\Validation\RuleGroup;
use Illuminate\Support\Str;

abstract class FormatBase
{
    /**
     * 转换规则
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return mixed
     */
    public function parse(Rule $rule)
    {
        $name = $rule->getName();
        if (!$name) {
            return null;
        }

        $method = Str::camel($name);
        if (method_exists($this, $method)) {
            return $this->$method($rule);
        }

        return $this->parseDefault($rule);
    }

    /**
     * 默认转换规则
     *
     * @param  \App\Services\Validation\Rule $rule
     * @return mixed
     */
    abstract protected function parseDefault(Rule $rule);

    /**
     * 转换规则集
     *
     * @param  \App\Services\Validation\RuleGroup $group
     * @return array
     */
    public function parseGroup(RuleGroup $group)
    {
        $results = [];
        foreach ($group as $rule) {
            if ($rule = $this->parse($rule)) {
                $results[] = $rule;
            }
        }

        return $results;
    }
}
