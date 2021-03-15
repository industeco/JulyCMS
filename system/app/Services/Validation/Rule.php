<?php

namespace App\Services\Validation;

use App\Services\Validation\RuleFormats\FormatBase;
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
        'pathAlias' => '网址格式不正确',
        'exists' => '已存在',
    ];

    /**
     * 规则作用的字段名
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
    public function __construct(string $rule, ?string $field = null)
    {
        $this->rule = $rule;
        $this->field = $field;

        $this->init();
    }

    /**
     * 解析规则
     *
     * @return $this
     */
    protected function init()
    {
        $rule = trim($this->rule ?? '');
        if (empty($rule)) {
            $this->name = '';
            $this->parameters = null;
            $this->message = null;

            return $this;
        }

        if (false === strpos($rule, '\\')) {
            $rule = explode(':', $rule);
        } else {
            $rule = str_replace('\\:', '{COLON}', str_replace('\\\\', '{SLASH}', $rule));
            $rule = str_replace(['{SLASH}', '{COLON}'], ['\\', ':'], explode(':', $rule));
        }

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
     * 获取规则参数
     *
     * @return string
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * 获取消息模板
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * 获取消息键
     *
     * @return string
     */
    public function getMessageKey()
    {
        return $this->field . '.' . $this->name;
    }

    /**
     * 将规则转换为指定格式
     *
     * @param  \App\Services\Validation\FormatBase\FormatBase $format
     * @return mixed
     */
    public function parseTo(FormatBase $format)
    {
        return $format->parse($this);
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
        if (preg_match('/\{.*?\}/', $message)) {
            $context = $context ?? [$this->name => $this->parameters];
            $message = preg_replace_callback('/\{(.*?)\}/', function ($maches) use ($context) {
                return $context[trim($maches[1])] ?? '{'.$maches[1].'}';
            }, $message);
        }

        return htmlspecialchars($message, ENT_QUOTES|ENT_HTML5);
    }

    public function __call($name, $arguments)
    {
        return null;
    }
}
