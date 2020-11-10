<?php

namespace App;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

abstract class Model extends EloquentModel
{
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
     * 快捷创建实例
     *
     * @return static
     */
    public static function make(array $attributes = [])
    {
        return new static($attributes);
    }

    /**
     * 排除指定属性
     *
     * @param array $columns
     * @return array
     */
    public function except(array $columns = [])
    {
        return Arr::except($this->attributesToArray(), $columns);
    }

    /**
     * {@inheritdoc}
     */
    public static function boot()
    {
        parent::boot();

        static::created(function(Model $model) {
            events()->record(static::class.':created');
            events()->record(static::class.':changed');
        });

        static::updated(function(Model $model) {
            events()->record(static::class.':updated');
            events()->record(static::class.':changed');
        });

        static::saved(function(Model $model) {
            events()->record(static::class.':saved');
        });

        static::deleted(function(Model $model) {
            events()->record(static::class.':deleted');
            events()->record(static::class.':changed');
        });
    }
}
