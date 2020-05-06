<?php

namespace App\Traits;

trait ExtractConfig
{
    abstract public static function configStructure(): array;

    public function extractConfig(array $langcode = [])
    {
        $structure = static::configStructure();
        $config = $this->config;
        $options = [];

        $original_langcode = $config['langcode'] ?? langcode();
        unset($config['langcode']);

        $langcode = $langcode ?: $original_langcode;

        foreach ($config as $key => $value) {
            $type = $structure[$key] ?? null;
            if ($type === 'content' && is_array($value)) {
                $lang = $langcode['content_value'] ?? $original_langcode['content_value'];
                $value = $value[$lang] ?? null;
            } elseif ($type === 'interface' && is_array($value)) {
                $lang = $langcode['interface_value'] ?? $original_langcode['interface_value'];
                $value = $value[$lang] ?? null;
            }
            $options[$key] = $value;
        }

        return $options;
    }
}
