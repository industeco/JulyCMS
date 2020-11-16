<?php

namespace July\Core\Entity;

interface EntityInterface
{
    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName();

    /**
     * 获取实体对象 id
     *
     * @return int|string
     */
    public function getEntityKey();

    /**
     * 获取实体路径，由实体名与实体实例的 id 组成
     *
     * @return string
     */
    public function getEntityPath();

    /**
     * 获取实体对象
     *
     * @param  mixed $id
     * @return \July\Core\Entity\EntityInterface|null
     */
    public static function find($id);

    /**
     * 获取实体对象，失败则抛出错误
     *
     * @param  mixed $id
     * @return \July\Core\Entity\EntityInterface
     *
     * @throws \July\Base\Exceptions\EntityNotFoundException
     */
    public static function findOrFail($id);

    /**
     * 判断实体是否可翻译
     *
     * @return bool
     */
    public static function isTranslatable();

    /**
     * 设置当前实例语言版本
     *
     * @param  string $langcode 语言代码
     * @return $this
     */
    public function translateTo(string $langcode);

    /**
     * 获取当前实例的语言
     *
     * @return string|null
     */
    public function getLangcode();

    /**
     * 集合实体的三类属性
     *
     * @return array
     */
    public function gather();

    /**
     * 获取固有属性集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectIntrinsicAttributes();

    /**
     * 判断是否包含名为 {$key} 的固有属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasIntrinsicAttribute($key);

    // /**
    //  * 获取固有属性的值
    //  *
    //  * @param  string $key
    //  * @return mixed
    //  */
    // public function getIntrinsicAttributeValue($key);

    /**
     * 获取所有固有属性的值
     *
     * @return array
     */
    public function intrinsicAttributesToArray();

    /**
     * 获取附加属性集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectAttachedAttributes();

    /**
     * 判断是否包含名为 {$key} 的附加属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasAttachedAttribute($key);

    // /**
    //  * 获取附加属性的值
    //  *
    //  * @param  string $key
    //  * @return mixed
    //  */
    // public function getAttachedAttributeValue($key);

    /**
     * 获取所有附加属性的值
     *
     * @return array
     */
    public function attachedAttributesToArray();

    /**
     * 获取实体字段对象集
     *
     * @return \Illuminate\Support\Collection
     */
    public function collectEntityFields();

    /**
     * 判断是否包含名为 {$key} 的实体字段
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasEntityField($key);

    // /**
    //  * 获取实体字段的值
    //  *
    //  * @param  string $key
    //  * @return mixed
    //  */
    // public function getEntityFieldValue($key);

    /**
     * 获取所有实体字段的值
     *
     * @return array
     */
    public function entityFieldsToArray();

    /**
     * 获取实体属性值
     *
     * @param  string $key 属性名
     * @return mixed
     */
    public function getEntityAttribute($key);
}
