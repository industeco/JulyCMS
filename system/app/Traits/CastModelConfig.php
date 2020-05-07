<?php

namespace App\Traits;

trait CastModelConfig
{
    abstract public function configStructure(): array;

    /**
     * 从多语言的配置数据中提取当前语言的配置值
     *
     * @param array $langcode
     * @return array
     */
    public function getConfigOptions(array $langcode = []): array
    {
        $config = $this->config;
        $structure = $this->configStructure();

        return extract_config($config, $structure, $langcode);
    }

    /**
     * @param array $data
     * @return array
     */
    public function buildConfig(array $data): array
    {
        return build_config($data, $this->configStructure());
    }
}
