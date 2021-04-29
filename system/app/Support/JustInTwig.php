<?php

namespace App\Support;

use Illuminate\Support\Traits\Macroable;

class JustInTwig
{
    use Macroable;

    protected static $translations = [];

    protected $globals = [];

    /**
     * 添加一个全局变量
     *
     * @param  string $name
     * @param  mixed $value
     * @return $this
     */
    public function addGlobal(string $name, $value)
    {
        $this->globals[$name] = $value;

        return $this;
    }

    public function mergeGlobals(array $context)
    {
        foreach ($this->getGlobals() as $key => $value) {
            if (!\array_key_exists($key, $context)) {
                $context[$key] = $value;
            }
        }

        $this->globals = $context;

        return $this;
    }

    /**
     * 获取一个全局变量
     *
     * @return array
     */
    public function getGlobal(string $name)
    {
        return $this->globals[$name] ?? null;
    }

    /**
     * 获取所有全局变量
     *
     * @return array
     */
    public function getGlobals()
    {
        return $this->globals;
    }

    /**
     * 移除一个变量
     *
     * @param  string $name
     * @return $this
     */
    public function removeGlobal(string $name)
    {
        unset($this->globals[$name]);

        return $this;
    }

    /**
     * 翻译
     *
     * @param  string $content
     * @param  string|null $langcode
     * @return string
     */
    public function trans(string $content, ?string $langcode = null)
    {
        $langcode = lang(
            $langcode ?? $this->globals['_langcode']
        )->getLangcode() ?? langcode('rendering') ?? langcode('frontend');

        return $this->getTranslations($langcode)[$content] ?? $content;
    }

    /**
     * 翻译函数别名
     *
     * @param  string $content
     * @param  string|null $langcode
     * @return string
     */
    public function __(string $content, ?string $langcode = null)
    {
        return $this->trans($content, $langcode);
    }

    /**
     * 获取翻译内容
     *
     * @param  string $langcode
     * @return array
     */
    public function getTranslations(string $langcode)
    {
        $langcode = strtolower($langcode);
        if ($translations = static::$translations[$langcode] ?? null) {
            return $translations;
        }

        $path = frontend_path('lang/'.$langcode.'.json');
        if (is_file($path)) {
            $translations = \json_decode(file_get_contents($path), true);
            static::$translations[$langcode] = $translations;
            return $translations;
        }

        return [];
    }

    /**
     * Determine if an attribute or relation exists on the model.
     *
     * @param  string  $key
     * @return bool
     */
    public function __isset($key)
    {
        return \array_key_exists($key, $this->globals);
    }

    public function __get($name)
    {
        return $this->globals[$name] ?? null;
    }
}
