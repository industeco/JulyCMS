<?php

namespace App\Models;

use App\Support\Arr;
use App\Traits\CacheModel;
use App\Traits\FetchModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class JulyModel extends Model
{
    use CacheModel, FetchModel;

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

    public static function make(array $attributes = [])
    {
        return new static($attributes);
    }

    public static function primaryKeyName()
    {
        return (new static)->getKeyName();
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
     * 获取常用属性
     *
     * @param string|null $langcode
     * @return array
     */
    public function gather($langcode = null)
    {
        return $this->attributesToArray();
    }
}
