<?php

namespace July\Core\Entity;

use App\Contracts\PocketableInterface;
use Illuminate\Contracts\Support\Renderable;

interface EntityInterface extends PocketableInterface, Renderable
{
    /**
     * 获取实体名
     *
     * @return string
     */
    public static function getEntityName();

    /**
     * 获取实体类型的实体名
     *
     * @return string|null
     */
    public static function getBundleName();

    /**
     * 获取实体对象 id
     *
     * @return int|string
     */
    public function getEntityId();

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
    public function isTranslatable();

    /**
     * 判断是否包含名为 {$key} 的实体属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasEntityAttribute(string $key);

    /**
     * 获取实体属性值
     *
     * @param  string $key 属性名
     * @return mixed
     */
    public function getEntityAttribute(string $key);

    /**
     * 集合实体的三类属性
     *
     * @return array
     */
    public function entityToArray();

    /**
     * 判断是否包含名为 {$key} 的列（内建属性）
     *
     * @param  string $key 列名
     * @return bool
     */
    public function hasColumn(string $key);

    /**
     * 获取列（内建属性）的值
     *
     * @param  string $key
     * @return mixed
     */
    public function getColumnValue(string $key);

    /**
     * 获取所有固有属性的值
     *
     * @return array
     */
    public function columnsToArray();

    /**
     * 判断是否包含名为 {$key} 的实体字段
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasField(string $key);

    /**
     * 获取实体字段的值
     *
     * @param  string $key
     * @return mixed
     */
    public function getFieldValue(string $key);

    /**
     * 获取所有实体字段的值
     *
     * @return array
     */
    public function fieldsToArray();

    /**
     * 登记外联属性
     *
     * @param  array $links
     */
    public static function registerLinks(array $links);

    /**
     * 判断是否包含名为 {$key} 的外联属性
     *
     * @param  string $key 属性名
     * @return bool
     */
    public function hasLink(string $key);

    /**
     * 获取附加属性的值
     *
     * @param  string $key
     * @return mixed
     */
    public function getLinkValue(string $key);

    /**
     * 获取所有附加属性的值
     *
     * @return array
     */
    public function linksToArray();



    // /**
    //  * 获取内建属性集
    //  *
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function collectColumns();

    // /**
    //  * 获取外联属性集
    //  *
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function collectLinks();

    // /**
    //  * 获取实体字段对象集
    //  *
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function collectFields();
}
