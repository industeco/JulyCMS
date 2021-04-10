<?php

namespace App\Models;

use App\Support\Arr;
use App\Support\Translation\TranslatableInterface;
use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use IteratorAggregate;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Enumerable;
use JsonSerializable;
use stdClass;
use Traversable;


abstract class ModelSetBase implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * 缓存的模型实例
     *
     * @var array
     */
    protected static $modelsCache = [];

    /**
     * The items contained in the collection.
     *
     * @var \App\Models\ModelSetBase[]
     */
    protected $models = [];

    /**
     * Create a new ModelSet.
     *
     * @param  \App\Models\ModelBase[] $models
     * @return void
     */
    public function __construct(array $models = [], $prepare = true)
    {
        $this->models = $prepare ? static::prepareModels($models) : $models;
    }

    /**
     * 获取绑定的实体类
     *
     * @return string
     */
    abstract public static function getModelClass();

    /**
     * @return bool
     */
    public static function isTranslatable()
    {
        return is_subclass_of(static::getModelClass(), TranslatableInterface::class);
    }

    /**
     * @param  \App\Models\ModelBase[] $models
     * @return \App\Models\ModelBase[]
     */
    protected static function prepareModels(array $models)
    {
        $class = static::getModelClass();

        $models = array_filter($models, function($model) use($class) {
            return $model instanceof $class;
        });

        if (static::isTranslatable() && $langcode = langcode('rendering')) {
            $models = array_map(function($model) use($langcode) {
                return $model->translateTo($langcode);
            }, $models);
        }

        $results = [];
        foreach ($models as $model) {
            $results[$model->getKey()] = $model;
        }

        return $results;
    }

    /**
     * 创建 ModelSet
     *
     * @param  mixed $args
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function collect($args = [])
    {
        $args = is_array($args) ? $args : func_get_args();

        return new static(static::findModels($args), false);
    }

    /**
     * 创建 ModelSet
     *
     * @param  mixed $args
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetch($args = [])
    {
        return static::collect($args);
    }

    /**
     * 创建 ModelSet，包含全部模型实例
     *
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function collectAll()
    {
        return new static(static::getModelClass()::all());
    }

    /**
     * 创建 ModelSet，包含全部模型实例
     *
     * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
     */
    public static function fetchAll()
    {
        return static::collectAll();
    }

    /**
     * 获取多个模型实例并缓存
     *
     * @param  mixed $args
     * @return \App\Models\ModelBase[]|array
     */
    protected static function findModels($args = [])
    {
        $args = is_array($args) ? $args : func_get_args();

        $models = [];
        foreach ($args as $arg) {
            if ($model = static::findModel($arg)) {
                $models[] = $model;
            }
        }

        return static::prepareModels($models);
    }

    /**
     * 获取模型实例并缓存
     *
     * @param  mixed $item
     * @return \App\Models\ModelBase|null
     */
    protected static function findModel($item)
    {
        $class = static::getModelClass();

        $model = is_object($item) && ($item instanceof $class)
                    ? $item
                    : (self::$modelsCache[$class][$item] ?? $class::find($item));

        if ($model) {
            self::$modelsCache[$class]['id_'.$model->getKey()] = $model;
        }

        return $model;
    }

    /**
     * 获取模型实例并缓存
     *
     * @param  mixed $item
     * @return \App\Models\ModelBase|null
     */
    protected static function resolve($item)
    {
        return static::findModel($item);
    }

    /**
     * @param  \App\Models\ModelBase[]  $models
     * @return static
     */
    protected function newModelSet(array $models = [])
    {
        return new static($models, false);
    }

    /**
     * @param  array  $items
     * @return \Illuminate\Support\Collection
     */
    protected function newCollection(array $items = [])
    {
        return new Collection($items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->models;
    }

    /**
     * Get a lazy collection for the items in this collection.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazy()
    {
        return new LazyCollection($this->models);
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param  mixed  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            if ($this->useAsCallable($key)) {
                $placeholder = new stdClass;

                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->models);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get the items in the collection that are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diff($items)
    {
        return $this->newModelSet(array_diff($this->models, static::findModels($items)));
    }

    /**
     * Get the items in the collection that are not present in the given items, using the callback.
     *
     * @param  mixed  $items
     * @param  callable  $callback
     * @return static
     */
    public function diffUsing($items, callable $callback)
    {
        return $this->newModelSet(array_udiff($this->models, static::findModels($items), $callback));
    }

    /**
     * Get the items in the collection whose keys and values are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffAssoc($items)
    {
        return $this->newModelSet(array_diff_assoc($this->models, static::findModels($items)));
    }

    /**
     * Get the items in the collection whose keys and values are not present in the given items, using the callback.
     *
     * @param  mixed  $items
     * @param  callable  $callback
     * @return static
     */
    public function diffAssocUsing($items, callable $callback)
    {
        return $this->newModelSet(array_diff_uassoc($this->models, static::findModels($items), $callback));
    }

    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffKeys($items)
    {
        return $this->newModelSet(array_diff_key($this->models, static::findModels($items)));
    }

    /**
     * Get the items in the collection whose keys are not present in the given items, using the callback.
     *
     * @param  mixed  $items
     * @param  callable  $callback
     * @return static
     */
    public function diffKeysUsing($items, callable $callback)
    {
        return $this->newModelSet(array_diff_ukey($this->models, static::findModels($items), $callback));
    }

    /**
     * Retrieve duplicate items from the collection.
     *
     * @param  callable|null  $callback
     * @param  bool  $strict
     * @return static
     */
    public function duplicates($callback = null, $strict = false)
    {
        return $this->newModelSet();
    }

    /**
     * Retrieve duplicate items from the collection using strict comparison.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function duplicatesStrict($callback = null)
    {
        return $this->newModelSet();
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param  \Illuminate\Support\Collection|mixed  $keys
     * @return static
     */
    public function except($keys)
    {
        if ($keys instanceof Enumerable) {
            $keys = $keys->all();
        } elseif (! is_array($keys)) {
            $keys = func_get_args();
        }

        return $this->newModelSet(Arr::except($this->models, $keys));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(callable $callback = null)
    {
        if ($callback) {
            return $this->newModelSet(Arr::where($this->models, $callback));
        }

        return $this->newModelSet(array_filter($this->models));
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return \App\Models\ModelBase|null
     */
    public function first(callable $callback = null, $default = null)
    {
        return Arr::first($this->models, $callback, $default);
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param  int  $depth
     * @return static
     */
    public function flatten($depth = INF)
    {
        return $this;
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param  string|array  $keys
     * @return $this
     */
    public function forget($keys)
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->models[$key];
        }

        return value($default);
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param  callable|string  $groupBy
     * @return \Illuminate\Support\Collection
     */
    public function groupBy($groupBy)
    {
        $groupBy = $this->valueRetriever($groupBy);

        $results = [];

        foreach ($this->models as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            if (! is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int) $groupKey : $groupKey;

                if (! array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = [];
                }

                $results[$groupKey][$key] = $value;
            }
        }

        foreach ($results as $group => $items) {
            $results[$group] = $this->newModelSet($items);
        }

        return $this->newCollection($results);
    }

    /**
     * Key an associative array by a field or using a callback.
     *
     * @param  callable|string  $keyBy
     * @return static
     */
    public function keyBy($keyBy)
    {
        $keyBy = $this->valueRetriever($keyBy);

        $results = [];

        foreach ($this->models as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }

            $results[$resolvedKey] = $item;
        }

        return $this->newCollection($results);
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function has($key)
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (! $this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersect($items)
    {
        return $this->newModelSet(array_intersect($this->models, static::findModels($items)));
    }

    /**
     * Intersect the collection with the given items by key.
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return $this->newModelSet(array_intersect_key(
            $this->models, static::findModels($items)
        ));
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->models);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return $this->newCollection(array_keys($this->models));
    }

    /**
     * Get the last item from the collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return \App\Models\ModelBase|null
     */
    public function last(callable $callback = null, $default = null)
    {
        return Arr::last($this->models, $callback, $default);
    }

    /**
     * Get the values of a given key.
     *
     * @param  string|array  $value
     * @param  string|null  $key
     * @return static
     */
    public function pluck($value, $key = null)
    {
        return $this->newCollection(Arr::pluck($this->models, $value, $key));
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->models);

        $items = array_map($callback, $this->models, $keys);

        return $this->newModelSet(array_combine($keys, $items));
    }

    /**
     * Run a dictionary map over the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @param  callable  $callback
     * @return \Illuminate\Support\Collection
     */
    public function mapToDictionary(callable $callback)
    {
        $dictionary = [];

        foreach ($this->models as $key => $item) {
            $pair = $callback($item, $key);

            $key = key($pair);

            $value = reset($pair);

            if (! isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }

            $dictionary[$key][] = $value;
        }

        return $this->newCollection($dictionary);
    }

    /**
     * Run an associative map over each of the items.
     *
     * The callback should return an associative array with a single key/value pair.
     *
     * @param  callable  $callback
     * @return \Illuminate\Support\Collection
     */
    public function mapWithKeys(callable $callback)
    {
        $result = [];

        foreach ($this->models as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return $this->newCollection($result);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        return $this->newModelSet(array_merge($this->models, static::findModels($items)));
    }

    /**
     * Recursively merge the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function mergeRecursive($items)
    {
        return $this->newModelSet(array_merge($this->models, static::findModels($items)));
    }

    /**
     * Create a collection by using this collection for keys and another for its values.
     *
     * @param  mixed  $values
     * @return \Illuminate\Support\Collection
     */
    public function combine($values)
    {
        return $this->newCollection(array_combine($this->keys(), $this->getArrayableItems($values)));
    }

    /**
     * Union the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function union($items)
    {
        return $this->newModelSet($this->models + static::findModels($items));
    }

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @param  int  $step
     * @param  int  $offset
     * @return static
     */
    public function nth($step, $offset = 0)
    {
        $new = [];

        $position = 0;

        foreach ($this->models as $key => $item) {
            if ($position % $step === $offset) {
                $new[$key] = $item;
            }

            $position++;
        }

        return $this->newModelSet($new);
    }

    /**
     * Get the items with the specified keys.
     *
     * @param  mixed  $keys
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return $this->newModelSet($this->models);
        }

        if ($keys instanceof Enumerable) {
            $keys = $keys->all();
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return $this->newModelSet(Arr::only($this->models, $keys));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->models);
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param  mixed  $value
     * @param  mixed  $key
     * @return $this
     */
    public function prepend($value, $key = null)
    {
        foreach (static::findModels($value) as $key => $model) {
            $this->models = Arr::prepend($this->models, $model, $key);
        }

        return $this;
    }

    /**
     * Push one or more items onto the end of the collection.
     *
     * @param  mixed  $values [optional]
     * @return $this
     */
    public function push(...$values)
    {
        $this->models = array_merge($this->models, static::findModels($values));

        return $this;
    }

    /**
     * Push all of the given items onto the collection.
     *
     * @param  iterable  $source
     * @return static
     */
    public function concat($source)
    {
        $args = [];
        foreach ($source as $item) {
            $args[] = $item;
        }

        $models = array_merge($this->models, static::findModels($args));

        return $this->newModelSet($models);
    }

    /**
     * Get and remove an item from the collection.
     *
     * @param  mixed  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->models, $key, $default);
    }

    /**
     * Put an item in the collection by key.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return $this
     */
    public function put($key, $value)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @param  int|null  $number
     * @return static|mixed
     *
     * @throws \InvalidArgumentException
     */
    public function random($number = null)
    {
        if (is_null($number)) {
            return Arr::random($this->models);
        }

        return new static(Arr::random($this->models, $number));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->models, $callback, $initial);
    }

    /**
     * Replace the collection items with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function replace($items)
    {
        return new static(array_replace($this->models, $this->getArrayableItems($items)));
    }

    /**
     * Recursively replace the collection items with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function replaceRecursive($items)
    {
        return new static(array_replace_recursive($this->models, $this->getArrayableItems($items)));
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse()
    {
        return new static(array_reverse($this->models, true));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param  mixed  $value
     * @param  bool  $strict
     * @return mixed
     */
    public function search($value, $strict = false)
    {
        if (! $this->useAsCallable($value)) {
            return array_search($value, $this->models, $strict);
        }

        foreach ($this->models as $key => $item) {
            if ($value($item, $key)) {
                return $key;
            }
        }

        return false;
    }

    /**
     * Get and remove the first item from the collection.
     *
     * @return mixed
     */
    public function shift()
    {
        return array_shift($this->models);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @param  int|null  $seed
     * @return static
     */
    public function shuffle($seed = null)
    {
        return new static(Arr::shuffle($this->models, $seed));
    }

    /**
     * Skip the first {$count} items.
     *
     * @param  int  $count
     * @return static
     */
    public function skip($count)
    {
        return $this->slice($count);
    }

    /**
     * Skip items in the collection until the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function skipUntil($value)
    {
        return new static($this->lazy()->skipUntil($value)->all());
    }

    /**
     * Skip items in the collection while the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function skipWhile($value)
    {
        return new static($this->lazy()->skipWhile($value)->all());
    }

    /**
     * Slice the underlying collection array.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @return static
     */
    public function slice($offset, $length = null)
    {
        return $this->newModelSet(array_slice($this->models, $offset, $length, true));
    }

    /**
     * Split a collection into a certain number of groups.
     *
     * @param  int  $numberOfGroups
     * @return static
     */
    public function split($numberOfGroups)
    {
        if ($this->isEmpty()) {
            return new static;
        }

        $groups = new static;

        $groupSize = floor($this->count() / $numberOfGroups);

        $remain = $this->count() % $numberOfGroups;

        $start = 0;

        for ($i = 0; $i < $numberOfGroups; $i++) {
            $size = $groupSize;

            if ($i < $remain) {
                $size++;
            }

            if ($size) {
                $groups->push(new static(array_slice($this->models, $start, $size)));

                $start += $size;
            }
        }

        return $groups;
    }

    /**
     * Chunk the collection into chunks of the given size.
     *
     * @param  int  $size
     * @return static
     */
    public function chunk($size)
    {
        if ($size <= 0) {
            return new static;
        }

        $chunks = [];

        foreach (array_chunk($this->models, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Sort through each item with a callback.
     *
     * @param  callable|int|null  $callback
     * @return static
     */
    public function sort($callback = null)
    {
        $items = $this->models;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback);

        return new static($items);
    }

    /**
     * Sort items in descending order.
     *
     * @param  int  $options
     * @return static
     */
    public function sortDesc($options = SORT_REGULAR)
    {
        $items = $this->models;

        arsort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param  callable|string  $callback
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
    {
        $results = [];

        $callback = $this->valueRetriever($callback);

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // and grab the corresponding values for the sorted keys from this array.
        foreach ($this->models as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options)
            : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->models[$key];
        }

        return new static($results);
    }

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param  callable|string  $callback
     * @param  int  $options
     * @return static
     */
    public function sortByDesc($callback, $options = SORT_REGULAR)
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sort the collection keys.
     *
     * @param  int  $options
     * @param  bool  $descending
     * @return static
     */
    public function sortKeys($options = SORT_REGULAR, $descending = false)
    {
        $items = $this->models;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys in descending order.
     *
     * @param  int  $options
     * @return static
     */
    public function sortKeysDesc($options = SORT_REGULAR)
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param  int  $offset
     * @param  int|null  $length
     * @param  mixed  $replacement
     * @return static
     */
    public function splice($offset, $length = null, $replacement = [])
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->models, $offset));
        }

        return new static(array_splice($this->models, $offset, $length, $replacement));
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param  int  $limit
     * @return static
     */
    public function take($limit)
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Take items in the collection until the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function takeUntil($value)
    {
        return new static($this->lazy()->takeUntil($value)->all());
    }

    /**
     * Take items in the collection while the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function takeWhile($value)
    {
        return new static($this->lazy()->takeWhile($value)->all());
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return \Illuminate\Support\Collection
     */
    public function values()
    {
        return new Collection(array_values($this->models));
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param  mixed  $items
     * @return array
     */
    protected function getArrayableItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Enumerable) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return (array) $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function useAsCallable($value)
    {
        return ! is_string($value) && is_callable($value);
    }

    /**
     * Get an operator checker callback.
     *
     * @param  string  $key
     * @param  string|null  $operator
     * @param  mixed  $value
     * @return \Closure
     */
    protected function operatorForWhere($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $value = true;

            $operator = '=';
        }

        if (func_num_args() === 2) {
            $value = $operator;

            $operator = '=';
        }

        return function ($item) use ($key, $operator, $value) {
            $retrieved = data_get($item, $key);

            $strings = array_filter([$retrieved, $value], function ($value) {
                return is_string($value) || (is_object($value) && method_exists($value, '__toString'));
            });

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) == 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':  return $retrieved == $value;
                case '!=':
                case '<>':  return $retrieved != $value;
                case '<':   return $retrieved < $value;
                case '>':   return $retrieved > $value;
                case '<=':  return $retrieved <= $value;
                case '>=':  return $retrieved >= $value;
                case '===': return $retrieved === $value;
                case '!==': return $retrieved !== $value;
            }
        };
    }

    /**
     * Get a value retrieving callback.
     *
     * @param  callable|string|null  $value
     * @return callable
     */
    protected function valueRetriever($value)
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->models);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->models);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->models);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->models[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $this->models[] = $value;
        } else {
            $this->models[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  string  $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->models[$key]);
    }

    //
}

// abstract class ModelSetBase extends Collection
// {
//     /**
//      * 缓存的模型实例
//      *
//      * @var array
//      */
//     protected static $modelsCache = [];

//     /**
//      * 获取绑定的实体类
//      *
//      * @return string
//      */
//     abstract public static function getModelClass();

//     /**
//      * Create a new collection.
//      *
//      * @param  mixed  $items
//      * @return void
//      */
//     public function __construct($items = [], $resolveModel = false)
//     {
//         $this->models = $this->getArrayableItems($items);

//         if ($resolveModel) {
//             // 解析模型
//             $items = $this->resolveModels($this->models);

//             // 模型主键名
//             $key = static::getModelClass()::getModelKeyName();

//             // 重新生成键名
//             $this->models = collect($items)->keyBy($key)->all();
//         }
//     }

//     /**
//      * 获取指定模型实例并缓存
//      *
//      * @param  array $args
//      * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
//      */
//     public static function fetch(...$args)
//     {
//         return new static(real_args($args), true);
//     }

//     /**
//      * 获取全部模型实例并缓存
//      *
//      * @return \App\Models\ModelSetBase|\App\Models\ModelBase[]
//      */
//     public static function fetchAll()
//     {
//         return new static(static::getModelClass()::all(), true);
//     }

//     /**
//      * 将参数转换为模型组
//      *
//      * @return \App\Models\ModelBase[]
//      */
//     protected function resolveModels(array $items)
//     {
//         if (empty($items)) {
//             return [];
//         }

//         // 绑定的模型
//         $class = static::getModelClass();

//         $cache = self::$modelsCache[$class] ?? [];

//         $models = [];
//         foreach ($items as $item) {
//             if (!is_object($item) || !($item instanceof $class)) {
//                 $item = $cache[$item] ?? $class::find($item);
//             }

//             if (is_object($item) && ($item instanceof $class)) {
//                 if (($item instanceof TranslatableInterface) && langcode('rendering')) {
//                     $item->translateTo(langcode('rendering'));
//                 }
//                 $models['id_'.$item->getKey()] = $item;
//             }
//         }

//         self::$modelsCache[$class] = array_merge($cache, $models);

//         return $models;
//     }
// }
