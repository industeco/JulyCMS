<?php

namespace July\Core\EntityField;

use App\Utils\Pocket;
use July\Core\Entity\EntityBase;
use July\Core\Entity\EntityManager;
use July\Core\EntityField\Exceptions\EntityNotSpecifiedException;

abstract class EntityFieldBase extends EntityBase implements EntityFieldInterface
{
    /**
     * @var \July\Core\Entity\EntityBase 字段所属实体
     */
    protected $entity;

    /**
     * 获取字段类型对象
     *
     * @return \July\Core\EntityField\FieldType
     */
    public function getFieldType()
    {
        return FieldType::findOrFail($this);
    }

    public function getLangcode()
    {
        if ($this->entity) {
            return $this->entity->getLangcode();
        }

        return $this->contentLangcode;
    }

    /**
     * 绑定实体
     *
     * @param  \July\Core\Entity\EntityBase $entity
     * @return static
     */
    public function bindEntity(EntityBase $entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function getBoundEntity()
    {
        return $this->entity;
    }

    public static function resolveParentEntityClass()
    {
        // 解析去掉末尾 _field 后的实体名
        return EntityManager::resolveName(substr(static::getEntityName(), 0, -6));
    }

    /**
     * 获取字段参数
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function parameters()
    {
        return $this->morphMany(FieldParameters::class, null, 'entity_name', 'field_id');
    }

    /**
     * 获取字段参数
     *
     * @return array
     */
    public function getParameters()
    {
        $langcode = $this->getLangcode();
        $bundleName = null;
        if ($pivot = $this->pivot) {
            $bundleName = $pivot->{$pivot->getForeignKey()};
        }

        $records = $this->parameters->keyBy(function(FieldParameters $item) {
            return $item->langcode.'|'.$item->bundle_name;
        });

        $keys = [$langcode.'|'.$bundleName, $langcode.'|'];

        foreach ($keys as $key) {
            if ($parameters = $records[$key] ?? null) {
                return $parameters->parameters;
            }
        }

        return [];
    }

    /**
     * 收集字段所有相关信息并组成数组
     *
     * @return array
     */
    public function gather()
    {
        $data = $this->attributesToArray();
        if ($pivot = $this->pivot) {
            $data['label'] = $pivot->label ?? $data['label'];
            $data['description'] = $pivot->description ?? $data['description'];
            $data['delta'] = (int) $pivot->delta;
        }

        $data['parameters'] = $this->getParameters();

        return $data;
    }

    /**
     * 设置字段值
     *
     * @param  mixed $value
     * @return void
     *
     * @throws \July\Core\EntityField\Exceptions\EntityNotSpecifiedException
     */
    public function setValue($value)
    {
        if (! $this->entity) {
            throw new EntityNotSpecifiedException;
        }

        // 清除字段值缓存
        Pocket::create($this)->clear('value/'.$this->entity->getLangcode());

        // 使用存取器设置值
        $this->getStorage()->set($value);
    }

    /**
     * 获取字段值
     *
     * @return mixed
     *
     * @throws \July\Core\EntityField\Exceptions\EntityNotSpecifiedException
     */
    public function getValue()
    {
        if (! $this->entity) {
            throw new EntityNotSpecifiedException;
        }

        $pocket = new Pocket($this);
        $key = 'value/'.$this->entity->getLangcode();

        if ($value = $pocket->get($key)) {
            return $value->value();
        }

        $value = $this->getStorage()->get();

        // 缓存字段值
        $pocket->put($key, $value);

        return $value;
    }

    /**
     * 删除字段值
     *
     * @return void
     *
     * @throws \July\Core\EntityField\Exceptions\EntityNotSpecifiedException
     */
    public function deleteValue()
    {
        if (! $this->entity) {
            throw new EntityNotSpecifiedException;
        }

        // 清除字段值缓存
        Pocket::create($this)->clear('value/'.$this->entity->getLangcode());

        // 使用存取器设置值
        $this->getStorage()->delete();
    }

    /**
     * 搜索字段值
     *
     * @param  string $needle 搜索该字符串
     * @return array
     */
    public function searchValue(string $needle)
    {
        return $this->getStorage()->search($needle);
    }

    public static function boot()
    {
        parent::boot();

        static::created(function(EntityFieldBase $field) {
            $field->getStorage()->tableUp();
        });

        static::deleted(function(EntityFieldBase $field) {
            $field->getStorage()->tableDown();
        });
    }

    // public function getValues(string $langcode = null)
    // {
    //     if (is_null($table = $this->tableName())) {
    //         //
    //         return [];
    //     }

    //     if ($langcode) {
    //         return DB::table($table)->where('langcode', $langcode)->get();
    //     } else {
    //         return DB::table($table)->get();
    //     }
    // }

    // public function getRecords(string $langcode = null)
    // {
    //     if (is_null($table = $this->tableName())) {
    //         return collect();
    //     }

    //     if ($langcode) {
    //         return DB::table($table)->where('langcode', $langcode)->get();
    //     } else {
    //         return DB::table($table)->get();
    //     }
    // }

    // /**
    //  * {@inheritdoc}
    //  */
    // public static function getParentEntityName()
    // {
    //     $parent = static::getParentEntityClass();
    //     return $parent::getEntityName();
    // }

    // public static function getEntityForeignKey()
    // {
    //     return variablize(static::getParentEntityName(), ['.' => '__']).'_id';
    // }
}
