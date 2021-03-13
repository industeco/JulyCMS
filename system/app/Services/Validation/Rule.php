<?php

namespace App\Services\Validation;

use App\Utils\Makable;
use Illuminate\Support\Str;

class Rule
{
    use Makable;

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
    ];

    /**
     * 规则所在的字段名
     *
     * @var string
     */
    protected $field;

    /**
     * 规则字符串
     *
     * @var string
     */
    protected $rule;

    /**
     * 规则名
     *
     * @var string|null
     */
    protected $name = null;

    /**
     * 规则参数
     *
     * @var string|null
     */
    protected $parameters = null;

    /**
     * 规则反馈消息
     *
     * @var string|null
     */
    protected $message = null;

    /**
     * 构造函数
     *
     * @param  string $rule
     * @param  string $field
     */
    public function __construct(string $rule, string $field)
    {
        $this->rule = $rule;
        $this->field = $field;

        $this->resolve();
    }

    /**
     * 解析规则
     *
     * @return $this
     */
    protected function resolve()
    {
        $rule = $this->rule ? explode(':', $this->rule) : [];

        $this->name = array_shift($rule);
        $this->message = count($rule) > 1 ? array_pop($rule) : null;
        $this->parameters = count($rule) > 0 ? implode(':', $rule) : null;

        return $this;
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

        return $this;
    }

    /**
     * 获取规则名
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * 获取规则名
     *
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 获取规则名
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 获取规则名
     *
     * @return string
     */
    public function getMessageKey()
    {
        return $this->field . '.' . $this->name;
    }

    /**
     * 获取规则的 Laravel 形式
     *
     * @return array|null
     */
    public function toLaravelRule()
    {
        if (!$this->name) {
            return null;
        }

        $rule = $this->{'get'.Str::studly($this->name).'LaravelRule'}() ?? [];
        return [
            $rule[0] ?? $this->name.($this->parameters ? ':'.$this->parameters : ''),
            [$this->getMessageKey() => $rule[1] ?? $this->resolveMessage()],
        ];
    }

    /**
     * 获取规则的 js 形式
     *
     * @param  array $rule
     * @return string|null
     */
    public function toJsRule()
    {
        if (!$this->name) {
            return null;
        }

        return $this->{'get'.Str::studly($this->name).'JsRule'}() ??
            "{type:'{$this->name}', message:'{$this->resolveMessage()}', trigger:'submit'}";
    }

    /**
     * 获取规则的反馈消息
     *
     * @param  array $rule
     * @param  array|null $context
     * @return string
     */
    public function resolveMessage(array $context = null)
    {
        $message = $this->message ?? static::$messageTemplates[$this->name] ?? '';
        if (empty($message) || !preg_match('/\{.*?\}/', $message)) {
            return $message;
        }

        $context = $context ?? [$this->name => $this->parameters];
        return preg_replace_callback('/\{(.*?)\}/', function ($maches) use ($context) {
            return $context[trim($maches[1])] ?? '{'.$maches[1].'}';
        }, $message);
    }

    /**
     * 获取 Laravel 版 required 规则
     *
     * @return array
     */
    protected function getRequiredLaravelRule()
    {
        return ['required', $this->resolveMessage()];
    }

    /**
     * 获取 js 版 required 规则
     *
     * @return string
     */
    protected function getRequiredJsRule()
    {
        return "{required:true, message:'{$this->resolveMessage()}', trigger:'submit'}";
    }

    /**
     * 获取 Laravel 版 max 规则
     *
     * @return array
     */
    protected function getMaxLaravelRule()
    {
        $max = (int) $this->parameters;

        return ['max:'.$max, $this->resolveMessage(compact('max'))];
    }

    /**
     * 获取 js 版 max 规则
     *
     * @return string
     */
    protected function getMaxJsRule()
    {
        $max = (int) $this->parameters;
        $message = $this->resolveMessage(compact('max'));

        return "{max:{$max}, message:'{$message}', trigger:'change'}";
    }

    /**
     * 获取 Laravel 版 email 规则
     *
     * @return array
     */
    protected function getEmailLaravelRule()
    {
        return ['email', $this->resolveMessage()];
    }

    /**
     * 获取 js 版 email 规则
     *
     * @return string
     */
    protected function getEmailJsRule()
    {
        return "{type:'email', message:'{$this->resolveMessage()}', trigger:'blur'}";
    }

    /**
     * 获取 Laravel 版 url 规则
     *
     * @return array
     */
    protected function getUrlLaravelRule()
    {
        return ['url', $this->resolveMessage()];
    }

    /**
     * 获取 js 版 url 规则
     *
     * @return string
     */
    protected function getUrlJsRule()
    {
        return "{type:'url', message:'{$this->resolveMessage()}', trigger:'blur'}";
    }

    /**
     * 获取 Laravel 版 pattern 规则
     *
     * @return array
     */
    protected function getPatternLaravelRule()
    {
        $pattern = trim($this->parameters);

        return ['regex:'.$pattern, $this->resolveMessage()];
    }

    /**
     * 获取 js 版 pattern 规则
     *
     * @return string
     */
    protected function getPatternJsRule()
    {
        $pattern = trim($this->parameters);

        return "{pattern:{$pattern}, message:'{$this->resolveMessage()}', trigger:'blur'}";
    }

    public function __call($name, $arguments)
    {
        return null;
    }
}
