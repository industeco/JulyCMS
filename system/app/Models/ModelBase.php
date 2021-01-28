<?php

namespace App\Models;

use App\Concerns\CacheResultTrait;
use App\Utils\Arr;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class ModelBase extends Model
{
    use CacheResultTrait;

    /**
     * 哪些字段可更新（白名单）
     *
     * @var array
     */
    protected $updateOnly = [];

    /**
     * 哪些字段不可更新（黑名单）
     *
     * @var array
     */
    protected $updateExcept = [];

    /**
     * 新建或更新时传入的原始数据
     *
     * @var array
     */
    protected $raw = [];

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \App\Models\ModelBase|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function find($id, array $columns = ['*'])
    {
        $instance = new static;

        return $instance->forwardCallTo($instance->newQuery(), 'find', [$id, $columns]);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return \App\Models\ModelBase|\Illuminate\Database\Eloquent\Collection|static|static[]
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function findOrFail($id, array $columns = ['*'])
    {
        $instance = new static;

        return $instance->forwardCallTo($instance->newQuery(), 'findOrFail', [$id, $columns]);
    }

    /**
     * Create and return an un-saved model instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function make(array $attributes = [])
    {
        return (new static)->newQuery()->make($attributes);
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public static function create(array $attributes = [])
    {
        return tap((new static)->newQuery()->make($attributes), function ($instance) {
            $instance->save();
        });
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     *
     * @throws \Illuminate\Database\Eloquent\MassAssignmentException
     */
    public function fill(array $attributes)
    {
        $this->raw = $attributes;

        parent::fill($attributes);

        return $this;
    }

    /**
     * Update the model in the database.
     *
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        if (! $this->exists) {
            return false;
        }

        if ($this->updateOnly) {
            $attributes = Arr::only($attributes, $this->updateOnly);
        } elseif ($this->updateExcept) {
            $attributes = Arr::except($attributes, $this->updateExcept);
        }

        return $this->fill($attributes)->save($options);
    }

    /**
     * 转换属性值
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributeValue($key, $value)
    {
        return $this->transformModelValue($key, $value);
    }

    /**
     * 转换属性数组
     *
     * @param  string $key
     * @param  mixed $value
     * @return mixed
     */
    protected function transformAttributesArray(array $attributes)
    {
        // If an attribute is a date, we will cast it to a string after converting it
        // to a DateTime / Carbon instance. This is so we will get some consistent
        // formatting while accessing attributes vs. arraying / JSONing a model.
        $attributes = $this->addDateAttributesToArray(
            $attributes = $this->getArrayableItems($attributes)
        );

        // Add the mutated attributes to the attributes array.
        $attributes = $this->addMutatedAttributesToArray(
            $attributes, $mutatedAttributes = $this->getMutatedAttributes()
        );

        // Handle any casts that have been setup for this model and cast
        // the values to their appropriate type. If the attribute has a mutator we
        // will not perform the cast on those attributes to avoid any confusion.
        $attributes = $this->addCastAttributesToArray(
            $attributes, $mutatedAttributes
        );

        return $attributes;
    }

    /**
     * 获取模型列表数据
     *
     * @return array[]
     */
    public static function index()
    {
        $primaryKey = (new static)->getKeyName();
        return static::all()->map(function(ModelBase $model) {
            return $model->gather();
        })->keyBy($primaryKey)->all();
    }

    /**
     * 获取模型模板数据
     *
     * @return array
     */
    public static function template()
    {
        return array_fill_keys((new static)->getFillable(), null);
    }

    /**
     * 排除指定属性
     *
     * @param  array $columns
     * @return array
     */
    public function except(array $columns = [])
    {
        return Arr::except($this->gather(), $columns);
    }

    /**
     * 获取属性集，可指定属性名
     *
     * @param  array $keys 属性白名单
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        if ($attributes = $this->cachePipe(__FUNCTION__)) {
            $attributes = $attributes->value();
        } else {
            $attributes = $this->attributesToArray();
        }
        if ($keys && $keys !== ['*']) {
            $attributes = Arr::only($attributes, $keys);
        }
        return $attributes;
    }
}
