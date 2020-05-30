<?php

namespace App\Contracts;

interface HasConfig
{
    /**
     * 获取配置数据的属性描述
     *
     * @return array
     */
    public function getConfigMeta(): array;

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
