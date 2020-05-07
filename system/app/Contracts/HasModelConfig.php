<?php

namespace App\Contracts;

interface HasModelConfig
{
    /**
     * 获取配置数据的结构
     *
     * @return array
     */
    public function configStructure(): array;

    /**
     * 从多语言的配置数据中提取当前语言的配置值
     *
     * @param array $langcode 目标语言
     * @return array
     */
    public function getConfigOptions(array $langcode = []): array;

    /**
     * 从给定数据中提取、构建配置
     *
     * @param array $data
     * @return array
     */
    public function buildConfig(array $data): array;

    /**
     * 构建配置数据时，根据配置结构对数值做相应转换
     *
     * @param mixed $value
     * @param string $cast
     * @return mixed
     */
    public static function castConfigOption($value, $cast);
}
