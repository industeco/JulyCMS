<?php

namespace App\Contracts;

interface HasConfig
{
    /**
     * 获取配置模式
     *
     * @return array
     */
    public function getConfigSchema(): array;

    /**
     * 从多语言的配置数据中提取当前语言的配置值
     *
     * @param array $langcode 目标语言
     * @return array
     */
    public function castConfigData(array $data, array $langcode = []): array;

    /**
     * 从给定数据中提取、构建配置
     *
     * @param array $data
     * @return array
     */
    public function extractConfig(array $data): array;
}
