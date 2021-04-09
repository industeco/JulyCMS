<?php

namespace App\Support;

class Lang
{
    /**
     * @var array
     */
    protected static $availableCache = [];

    /**
     * @var string|null
     */
    protected $alias = null;

    /**
     * @var string|null
     */
    protected $langcode = null;

    /**
     * @var boolean|null
     */
    protected $available = null;

    public function __construct(?string $alias = null)
    {
        $this->wrap($alias);
    }

    /**
     * 快捷创建 Lang 对象
     *
     * @param  string|null $alias
     * @return \App\Support\Lang
     */
    public static function make(?string $alias = null)
    {
        return new static($alias);
    }

    /**
     * 更换缺省语言代码
     *
     * @param  string|null $alias
     * @return $this
     */
    public function wrap(?string $alias = null)
    {
        $this->alias = $alias;
        $this->langcode = static::resolve(trim($alias));

        return $this;
    }

    /**
     * 根据别名返回语言代码
     *
     * @param string $alias
     * @return string|null
     */
    public static function resolve(string $alias)
    {
        $code = $alias;
        switch ($alias) {
            // 请求携带的语言设定
            case 'request':
            case 'request.original':
                $code = request('langcode');
                break;

            // 默认的内容语言
            case 'content':
            case 'content.default':
                $code = config('lang.content');
                break;

            // 默认的前端语言
            case 'frontend':
            case 'frontend.default':
                $code = config('lang.frontend');
                break;

            // 默认的后端语言
            case 'backend':
            case 'backend.default':
                $code = config('lang.backend');
                break;

            // 渲染时语言
            case 'rendering':
                $code = config('lang.rendering');
                break;
        }

        // 获取正确形式
        if ($code) {
            if (array_key_exists($code, config('lang.all'))) {
                return $code;
            }
            foreach (array_keys(config('lang.all')) as $langcode) {
                if (strcasecmp($code, $langcode) === 0) {
                    return $langcode;
                }
            }
        }

        return null;
    }

    /**
     * 获取语言名称列表
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getLangnames(?string $langcode = null)
    {
        $langcode = static::make($langcode ?: config('lang.backend'))->getCode();

        if ($names = config('lang.names.'.$langcode)) {
            return $names;
        }

        $file = base_path('language/'.$langcode.'.php');
        if (is_file($file)) {
            $names = require $file;
            config()->set('lang.names.'.$langcode, $names);
            return $names;
        }

        return [];
    }

    /**
     * 获取所有可用的语言代码
     *
     * @return array
     */
    public static function getAvailableLangcodes()
    {
        return array_keys(config('lang.available'));
    }

    /**
     * 获取所有可访问的语言代码
     *
     * @return array
     */
    public static function getAccessibleLangcodes()
    {
        $langcodes = [];
        foreach (config('lang.available') as $code => $settings) {
            if ($settings['accessible'] ?? false) {
                $langcodes[] = $code;
            }
        }

        return $langcodes;
    }

    /**
     * 获取所有可翻译的语言代码
     *
     * @return array
     */
    public static function getTranslatableLangcodes()
    {
        $langcodes = [];
        foreach (config('lang.available') as $code => $settings) {
            if ($settings['translatable'] ?? false) {
                $langcodes[] = $code;
            }
        }
        return $langcodes;
    }

    /**
     * 获取所有可用的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getAvailableLangnames(?string $langcode = null)
    {
        $names = static::getLangnames($langcode);

        $langnames = [];
        foreach (static::getAvailableLangcodes() as $code) {
            $langnames[$code] = $names[$code] ?? $code;
        }

        return $langnames;
    }

    /**
     * 获取所有可访问的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getAccessibleLangnames(?string $langcode = null)
    {
        $names = static::getLangnames($langcode);

        $langnames = [];
        foreach (static::getAccessibleLangcodes() as $code) {
            $langnames[$code] = $names[$code] ?? $code;
        }

        return $langnames;
    }

    /**
     * 获取所有可翻译的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getTranslatableLangnames(?string $langcode = null)
    {
        $names = static::getLangnames($langcode);

        $langnames = [];
        foreach (static::getTranslatableLangcodes() as $code) {
            $langnames[$code] = $names[$code] ?? $code;
        }

        return $langnames;
    }

    /**
     * 返回语言代码
     *
     * @return string|null
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * 返回语言代码
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->langcode;
    }

    /**
     * 返回语言方向
     *
     * @return string|null 'ltr'|'rtl'|null
     */
    public function getDir()
    {
        if (! $this->langcode) {
            return null;
        }

        return config('lang.all.'.$this->langcode.'.dir', 'ltr');
    }

    /**
     * 返回语言名称
     *
     * @param string|null $langcode 名称的语言版本
     * @return string|null
     */
    public function getLangname(?string $langcode = null)
    {
        if (! $this->langcode) {
            return null;
        }

        if ($langcode === 'native') {
            return $this->getNativeName();
        }

        $names = static::getLangnames($langcode);
        return $names[$this->langcode] ?? $this->langcode;
    }

    /**
     * 返回语言名称
     *
     * @param string|null $langcode 名称的语言版本
     * @return string|null
     */
    public function getName(?string $langcode = null)
    {
        return $this->getLangname($langcode);
    }

    /**
     * 返回语言自称
     *
     * @return string|null
     */
    public function getNativeName()
    {
        if (! $this->langcode) {
            return null;
        }

        return config('lang.all.'.$this->langcode.'.name.native') ?? $this->langcode;
    }

    /**
     * 判断是否可用
     *
     * @return boolean
     */
    public function isAvailable()
    {
        if (! $this->langcode) {
            return false;
        }

        return array_key_exists($this->langcode, config('lang.available'));
    }

    /**
     * 判断是否可访问
     *
     * @return boolean
     */
    public function isAccessible()
    {
        if (! $this->langcode) {
            return false;
        }

        return config('lang.available.'.$this->langcode.'.accessible', false);
    }

    /**
     * 判断是否可翻译
     *
     * @return boolean
     */
    public function isTranslatable()
    {
        if (! $this->langcode) {
            return false;
        }

        return config('lang.available.'.$this->langcode.'.translatable', false);
    }
}
