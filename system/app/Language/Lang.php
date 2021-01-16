<?php

namespace App\Language;

class Lang
{
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
        $alias = trim($alias);
        if ($alias) {
            $this->langcode = static::resolveAlias($alias) ?? $alias;
        }
    }

    /**
     * 快捷创建 Lang 对象
     *
     * @param  string|null $alias
     * @return \App\Language\Lang
     */
    public static function make(?string $alias = null)
    {
        return new static($alias);
    }

    /**
     * 更换缺省语言代码
     *
     * @param  string $alias
     * @return \App\Language\Lang
     */
    public function wrap(string $alias)
    {
        return new static($alias);
    }

    /**
     * 根据别名返回语言代码
     *
     * @param string $alias
     * @return string|null
     */
    public static function resolveAlias(string $alias)
    {
        switch ($alias) {
            // 内容语言
            case 'content':
                return config('lang.request_content') ?: config('lang.content');

            // 默认的内容语言
            case 'content.default':
                return config('lang.content');

            // 前端语言
            case 'frontend':
                if (! config('states.is_management_route')) {
                    return config('lang.request') ?: config('lang.frontend');
                }
                return config('lang.frontend');

            // 默认的前端语言
            case 'frontend.default':
                return config('lang.frontend');

            // 后端语言/默认的后端语言
            case 'backend':
            case 'backend.default':
                return config('lang.backend');

            // 请求的语言
            case 'request':
                return config('lang.request') ??
                    (config('states.is_management_route')
                        ? config('lang.backend')
                        : config('lang.frontend'));

            // 原始的请求语言
            case 'request.original':
                return config('lang.request');
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
        $langcode = $langcode ?: config('lang.backend');

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
    public function getCode()
    {
        if ($this->isAvailable()) {
            return $this->langcode;
        }
        return null;
    }

    /**
     * 返回语言方向
     *
     * @return string|null 'ltr'|'rtl'|null
     */
    public function getDir()
    {
        if ($this->isAvailable()) {
            return config('lang.all.'.$this->langcode.'.dir', 'ltr');
        }
        return null;
    }

    /**
     * 返回语言名称
     *
     * @param string|null $langcode 名称的语言版本
     * @return string|null
     */
    public function getName(?string $langcode = null)
    {
        if ($langcode === 'native') {
            return $this->getNativeName();
        }

        if ($this->isAvailable()) {
            $names = static::getLangnames($langcode);
            return $names[$this->langcode] ?? $this->langcode;
        }

        return null;
    }

    /**
     * 返回语言的自称
     *
     * @return string|null
     */
    public function getNativeName()
    {
        if ($this->isAvailable()) {
            return config('lang.all.'.$this->langcode.'.name.native', 'ltr');
        }
        return null;
    }

    /**
     * 判断是否有效的语言代码
     *
     * @return boolean
     */
    public function isAvailable()
    {
        // 检查缓存的 available 判断
        if (! is_null($this->available)) {
            return $this->available;
        }

        // 检查 langcode 本身
        if (! $this->langcode) {
            return $this->available = false;
        }

        // 检查 lang.available 数组
        if (config()->has('lang.available.'.$this->langcode)) {
            return $this->available = true;
        }

        // 忽略大小写检查 lang.available 数组
        foreach (array_keys(config('lang.available')) as $code) {
            if (strcasecmp($this->langcode, $code) == 0) {
                $this->langcode = $code;
                return $this->available = true;
            }
        }

        // 返回错误
        return $this->available = false;
    }

    /**
     * 判断当前语言代码是否可访问
     *
     * @return boolean
     */
    public function isAccessible()
    {
        return $this->isAvailable() && config('lang.available.'.$this->langcode.'.accessible', false);
    }

    /**
     * 判断当前语言代码是否可翻译
     *
     * @return boolean
     */
    public function isTranslatable()
    {
        return $this->isAvailable() && config('lang.available.'.$this->langcode.'.translatable', false);
    }
}
