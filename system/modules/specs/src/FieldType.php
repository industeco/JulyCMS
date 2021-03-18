<?php

namespace Specs;

use App\Support\Pocket;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;
use Specs\Exceptions\FieldTypeNotFoundException;
use Specs\FieldTypeDefinitions\DefinitionInterface;

class FieldType
{
    /**
     * 可用的字段类型定义
     *
     * @var array
     */
    protected static $definitions = [];

    /**
     * 获取字段类型定义类实例
     *
     * @param  string $id 类型定义 id
     * @return \Specs\FieldTypeDefinitions\DefinitionInterface|null
     */
    public static function find(string $id)
    {
        if (empty(static::$definitions)) {
            static::takeoutDefinitions();
        }

        if ($definition = static::$definitions[$id] ?? null) {
            return new $definition;
        }

        return null;
    }

    /**
     * 获取字段类型定义类实例，失败则抛出错误
     *
     * @param  string $id 类型定义 id
     * @return \Specs\FieldTypeDefinitions\DefinitionInterface
     *
     * @throws \Specs\Exceptions\FieldTypeNotFoundException
     */
    public static function findOrFail(string $id)
    {
        if ($definition = static::find($id)) {
            return $definition;
        }

        throw new FieldTypeNotFoundException();
    }

    /**
     * 获取字段类型列表
     *
     * @return \Illuminate\Support\Collection|\Specs\FieldTypeDefinitions\DefinitionInterface[]
     */
    public static function all()
    {
        if (empty(static::$definitions)) {
            static::takeoutDefinitions();
        }

        return collect(static::$definitions)->map(function($definition) {
            return new $definition;
        });
    }

    /**
     * 从缓存或文件系统搜集字段类型定义类
     *
     * @return void
     */
    protected static function takeoutDefinitions()
    {
        if (config('app.env') !== 'production') {
            static::$definitions = static::discoverDefinitions();
            return;
        }

        $pocket = Pocket::make(static::class)->setKey('definitions');
        if ($definitions = $pocket->get()) {
            static::$definitions = $definitions->value();
        } else {
            $pocket->put(static::$definitions = static::discoverDefinitions());
        }
    }

    /**
     * 从文件系统搜集字段类型定义类
     *
     * @return array
     */
    protected static function discoverDefinitions()
    {
        $definitions = [];

        $path = __DIR__.\DIRECTORY_SEPARATOR.'FieldTypeDefinitions';
        $prefix = 'Specs\\FieldTypeDefinitions\\';

        foreach (Finder::create()->files()->name('*.php')->in($path) as $file) {
            $class = $prefix.$file->getBasename('.php');
            if (static::isDefinitionClass($class)) {
                $definitions[(new $class)->getId()] = $class;
            }
        }

        return $definitions;
    }

    /**
     * 判断一个类是否类型定义类
     *
     * @param  string $class
     * @return bool
     */
    public static function isDefinitionClass(string $class)
    {
        if (! class_exists($class)) {
            return false;
        }

        $ref = new \ReflectionClass($class);
        return $ref->isInstantiable() && $ref->implementsInterface(DefinitionInterface::class);
    }
}
