<?php

namespace App\Models;

use App\Support\CacheResultTrait;
use App\Support\Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

abstract class ModelBase extends Model
{
    use CacheResultTrait;

    /**
     * 不可更新字段
     *
     * @var array
     */
    protected $immutable = [];

    /**
     * 新建或更新时传入的原始数据
     *
     * @var array
     */
    protected $raw = [];

    /**
     * 获取对应的模型集类
     *
     * @return string|null
     */
    public static function getModelSetClass()
    {
        return null;
    }

    public static function isTranslatable()
    {
        return false;
    }

    /**
     * 获取并缓存
     *
     * @param  mixed  $id
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public static function fetch($id)
    {
        if ($modelSet = static::getModelSetClass()) {
            if (is_array($id)) {
                return $modelSet::fetch($id);
            }
            return $modelSet::fetch($id)->first();
        }
        return static::find($id);
    }

    /**
     * 获取所有并缓存
     *
     * @return \App\Models\ModelSetBase|\Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function fetchAll()
    {
        if ($modelSet = static::getModelSetClass()) {
            return $modelSet::fetchAll();
        }
        return static::all();
    }

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

        return parent::fill($attributes);
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

        if ($this->immutable) {
            $attributes = Arr::except($attributes, $this->immutable);
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
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if ($this->fireModelEvent('creating') === false) {
            return false;
        }

        // First we'll need to create a fresh query instance and touch the creation and
        // update timestamps on this model, which are maintained by us for developer
        // convenience. After, we will just continue saving these model instances.
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        // If the model has an incrementing key, we can use the "insertGetId" method on
        // the query builder, which will give us back the final inserted ID for this
        // table from the database. Not all tables have to be incrementing though.
        $attributes = $this->getModelAttributes();

        if ($this->getIncrementing()) {
            $this->insertAndSetId($query, $attributes);
        }

        // If the table isn't incrementing we'll simply insert these attributes as they
        // are. These attribute arrays must contain an "id" column previously placed
        // there by the developer as the manually determined key for these models.
        else {
            if (empty($attributes)) {
                return true;
            }

            $query->insert($attributes);
        }

        // We will go ahead and set the exists property to true, so that it is set when
        // the created event is fired, just in case the developer tries to update it
        // during the event. This will allow them to do so and run an update here.
        $this->exists = true;

        $this->wasRecentlyCreated = true;

        $this->fireModelEvent('created', false);

        return true;
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach (Arr::except($this->getModelAttributes(), $this->immutable) as $key => $value) {
            if (! $this->originalIsEquivalent($key)) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getModelAttributes()
    {
        $this->mergeAttributesFromClassCasts();

        return $this->attributes;
    }

    /**
     * Clone the model into a new, non-existing instance.
     *
     * @param  array|null  $except
     * @return static
     */
    public function replicate(array $except = null)
    {
        $defaults = [
            $this->getKeyName(),
            $this->getCreatedAtColumn(),
            $this->getUpdatedAtColumn(),
        ];

        $attributes = Arr::except(
            $this->getModelAttributes(), $except ? array_unique(array_merge($except, $defaults)) : $defaults
        );

        return tap(new static, function ($instance) use ($attributes) {
            $instance->setRawAttributes($attributes);

            $instance->setRelations($this->relations);

            $instance->fireModelEvent('replicating', false);
        });
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->getModelAttributes();

        return $this;
    }

    /**
     * Sync multiple original attribute with their current values.
     *
     * @param  array|string  $attributes
     * @return $this
     */
    public function syncOriginalAttributes($attributes)
    {
        $attributes = is_array($attributes) ? $attributes : func_get_args();

        $modelAttributes = $this->getModelAttributes();

        foreach ($attributes as $attribute) {
            $this->original[$attribute] = $modelAttributes[$attribute];
        }

        return $this;
    }

    /**
     * 获取更新时间
     */
    public function getUpdatedAt()
    {
        return $this->{$this->getUpdatedAtColumn()};
    }

    /**
     * 获取创建时间
     */
    public function getCreatedAt()
    {
        return $this->{$this->getCreatedAtColumn()};
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function setRaw(?array $raw = null)
    {
        $this->raw = $raw;

        return $this;
    }

    public function clearRaw()
    {
        $this->raw = null;

        return $this;
    }

    /**
     * 获取模型列表数据
     *
     * @param  array $columns 选取的列
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public static function index(array $columns = ['*'])
    {
        return static::query()->get($columns)->keyBy(static::getModelKeyName());
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
     * 获取属性集，可指定属性名单
     *
     * @param  array $keys 属性白名单
     * @return array
     */
    public function gather(array $keys = ['*'])
    {
        // 尝试从缓存获取数据
        if ($attributes = $this->pocket()->get('gather')) {
            $attributes = $attributes->value();
        }

        // 生成属性数组
        else {
            $attributes = $this->attributesToArray();
            $this->pocket('gather')->put($attributes);
        }

        if ($keys && $keys !== ['*']) {
            $attributes = Arr::only($attributes, $keys);
        }

        return $attributes;
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        if (Str::startsWith($method, 'getModel')) {
            $method = 'get'.substr($method, strlen('getModel'));
        }

        return (new static)->$method(...$parameters);
    }
}
