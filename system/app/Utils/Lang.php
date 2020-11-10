<?php

namespace App\Utils;

class Lang
{
    /**
     * @var string|null
     */
    protected $langcode = null;

    public function __construct($langcode = null)
    {
        if ($langcode) {
            $this->langcode = static::findLangcode($langcode);
        }
    }

    public static function make($langcode = null)
    {
        return new static($langcode);
    }

    /**
     * 根据别名返回语言代码
     *
     * @param string $alias
     * @return string
     */
    public static function findLangcode(string $alias)
    {
        switch ($alias) {
            // 内容语言
            case 'content':
                return config('request.language.content') ?: config('jc.language.content');

            // 默认的内容语言
            case 'content.default':
                return config('jc.language.content');

            // 前台页面语言
            case 'frontend':
            case 'page.frontend':
            case 'page':
                return config('request.language.frontend') ?: config('jc.language.frontend');

            // 默认的前台页面语言
            case 'frontend.default':
            case 'page.frontend.default':
            case 'page.default':
                return config('jc.language.frontend');

            // 后台页面语言
            case 'backend':
            case 'page.backend':
            case 'backend.default':
            case 'page.backend.default':
                return config('jc.language.backend');

            // 请求语言数组
            case 'request':
                return config('request.language');
        }

        return static::isLangcode($alias) ? $alias : null;
    }

    /**
     * 判断是否语言代码
     *
     * @param string $langcode
     * @return boolean
     */
    public static function isLangcode($langcode)
    {
        return $langcode && array_key_exists($langcode, static::getLanguageList());
    }

    /**
     * 获取当前语言代码
     *
     * @return string|null
     */
    public function getCode()
    {
        return $this->langcode;
    }

    /**
     * 获取当前语言代码对应的语言全称
     *
     * @param string|null $langcode 全称的语言版本
     * @return string|null
     */
    public function getName($langcode = null)
    {
        if (! $this->langcode) {
            return null;
        }
        $list = static::getLanguageList($langcode);
        return $list[$this->langcode] ?? $this->langcode;
    }

    /**
     * 判断当前语言代码是否可用
     *
     * @return boolean
     */
    public function isAvailable()
    {
        return $this->langcode && array_key_exists($this->langcode, config('jc.language.list'));
    }

    /**
     * 判断当前语言代码是否可访问
     *
     * @return boolean
     */
    public function isAccessible()
    {
        return $this->langcode && config('jc.language.list.'.$this->langcode.'.accessible', false);
    }

    /**
     * 判断当前语言代码是否可翻译
     *
     * @return boolean
     */
    public function isTranslatable()
    {
        return $this->langcode && config('jc.language.list.'.$this->langcode.'.translatable', false);
    }

    /**
     * 获取语言列表
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getLanguageList($langcode = null)
    {
        $langcode = $langcode ?: static::findLangcode('backend');

        if ($list = config('language_list.'.$langcode, [])) {
            return $list;
        }

        $file = base_path('language/'.$langcode.'.php');
        if (is_file($file)) {
            $list = require $file;
            app('config')->set('language_list.'.$langcode, $list);
        }

        return $list;
    }

    /**
     * 获取所有可用的语言代码
     *
     * @return array
     */
    public static function getAvailableLangcodes()
    {
        return array_keys(config('jc.language.list'));
    }

    /**
     * 获取所有可访问的语言代码
     *
     * @return array
     */
    public static function getAccessibleLangcodes()
    {
        $langcodes = [];
        foreach (config('jc.language.list') as $code => $settings) {
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
        foreach (config('jc.language.list') as $code => $settings) {
            if ($settings['translatable'] ?? false) {
                $langcodes[] = $code;
            }
        }
        return $langcodes;
    }

    /**
     * 获取可用的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getAvailableLanguages($langcode = null)
    {
        $list = static::getLanguageList($langcode);

        $languages = [];
        foreach (static::getAvailableLangcodes() as $code) {
            $languages[$code] = $list[$code] ?? $code;
        }

        return $languages;
    }

    /**
     * 获取可访问的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getAccessibleLanguages($langcode = null)
    {
        $list = static::getLanguageList($langcode);

        $languages = [];
        foreach (static::getAccessibleLangcodes() as $code) {
            $languages[$code] = $list[$code] ?? $code;
        }

        return $languages;
    }

    /**
     * 获取可翻译的语言
     *
     * @param string|null 列表的语言版本
     * @return array
     */
    public static function getTranslatableLanguages($langcode = null)
    {
        $list = static::getLanguageList($langcode);

        $languages = [];
        foreach (static::getTranslatableLangcodes() as $code) {
            $languages[$code] = $list[$code] ?? $code;
        }

        return $languages;
    }

    /**
     * 允许通过对象访问静态方法
     */
    public function __call($name, $arguments)
    {
        return static::$name(...$arguments);
    }
}
