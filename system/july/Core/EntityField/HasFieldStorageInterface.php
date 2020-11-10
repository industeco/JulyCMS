<?php

namespace July\Core\EntityField;

interface HasFieldStorageInterface
{
    /**
     * 获取字段值存取器
     *
     * @return \July\Core\EntityField\FieldStorageBase
     */
    public function getStorage();
}
