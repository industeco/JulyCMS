<?php

namespace App\Support;

use App\Support\Arr;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Enumerable;

/**
 * Set：只允许特定类型的集合
 */
abstract class SetBase implements \ArrayAccess, \Countable, \IteratorAggregate
{
    const PROCESS_NONE = 0;
    const PROCESS_COLLECT = 1;
    const PROCESS_FILTER = 2;
    const PROCESS_INDEX = 4;

    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param  mixed  $items
     * @param  int  $options
     * @return void
     */
    public function __construct($items = [], $options = 1)
    {
        if (empty($items)) {
            return;
        }

        $this->items = static::processItems($items, $options);
    }

    /**
     * @param  mixed  $items
     * @param  int $process = 0
     * @return array
     */
    protected static function processItems($items, $process)
    {
        $items = Arr::from($items);

        if (! $process) {
            return $items;
        }

        if ($process & static::PROCESS_COLLECT) {
            return static::resolveItems($items);
        }

        if ($process & static::PROCESS_FILTER) {
            $items = static::filterItems($items);
        }

        if ($process & static::PROCESS_INDEX) {
            $items = static::indexItems($items);
        }

        return $items;
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  mixed  $items
     * @return static
     */
    public static function make($items = [], $options = 1)
    {
        return new static($items, $options);
    }

    /**
     * 使用当前集合的子集，或其它可信任项目，生成新的集合
     *
     * @param  mixed $items 当前集合的子集，或其它可信任项目的集合
     * @param  int $options = 0
     * @return static
     */
    public static function subset($items = [], $options = 0)
    {
        return new static($items, $options);
    }

    /**
     * 验证是否可作为集合项
     *
     * @param  mixed $item
     * @return bool
     */
    abstract public static function isValidItem($item);

    /**
     * 获取集合项的键
     *
     * @param  mixed $item
     * @return string|int|null
     */
    public static function getItemKey($item)
    {
        return null;
    }

    /**
     * 从给定数据中解析符合集合类型的项目
     *
     * @param  mixed $item
     * @return mixed
     */
    public static function resolveItem($item)
    {
        return static::isValidItem($item) ? $item : null;
    }

    /**
     * 从给定数据中解析符合集合类型的项目
     *
     * @param  mixed $items
     * @return array
     */
    public static function resolveItems($items = [])
    {
        $results = [];

        foreach (Arr::from($items) as $item) {
            if ($item = static::resolveItem($item)) {
                $results[] = $item;
            }
        }

        return static::indexItems($results);
    }

    /**
     * 过滤掉不符合集合类型的项目
     *
     * @param  array $items
     * @return array
     */
    public static function filterItems(array $items)
    {
        return array_filter($items, function($item) {
            return static::isValidItem($item);
        });
    }

    /**
     * 索引集合项目
     *
     * @param  array $items
     * @return array
     */
    public static function indexItems(array $items)
    {
        $keys = [];
        foreach ($items as $index => $item) {
            $keys[] = static::getItemKey($item) ?? $index;
        }

        return array_combine($keys, $items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Get a lazy collection for the items in this collection.
     *
     * @return \Illuminate\Support\LazyCollection
     */
    public function lazy()
    {
        return new LazyCollection($this->items);
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
                $placeholder = new \stdClass;

                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->items);
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
        return static::subset(array_diff($this->items, static::resolveItems($items)));
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
        return static::subset(array_udiff($this->items, static::resolveItems($items), $callback));
    }

    /**
     * Get the items in the collection whose keys and values are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffAssoc($items)
    {
        return static::subset(array_diff_assoc($this->items, static::resolveItems($items)));
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
        return static::subset(array_diff_uassoc($this->items, static::resolveItems($items), $callback));
    }

    /**
     * Get the items in the collection whose keys are not present in the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function diffKeys($items)
    {
        return static::subset(array_diff_key($this->items, static::resolveItems($items)));
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
        return static::subset(array_diff_ukey($this->items, static::resolveItems($items), $callback));
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

        return static::subset(Arr::except($this->items, $keys));
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
            return static::subset(Arr::where($this->items, $callback));
        }

        return static::subset(array_filter($this->items));
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
        return Arr::first($this->items, $callback, $default);
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
            return $this->items[$key];
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

        foreach ($this->items as $key => $value) {
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
            $results[$group] = static::subset($items);
        }

        return new Collection($results);
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

        foreach ($this->items as $key => $item) {
            $resolvedKey = $keyBy($item, $key);

            if (is_object($resolvedKey)) {
                $resolvedKey = (string) $resolvedKey;
            }

            $results[$resolvedKey] = $item;
        }

        return new Collection($results);
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
        return static::subset(array_intersect($this->items, static::resolveItems($items)));
    }

    /**
     * Intersect the collection with the given items by key.
     *
     * @param  mixed  $items
     * @return static
     */
    public function intersectByKeys($items)
    {
        return static::subset(array_intersect_key(
            $this->items, static::resolveItems($items)
        ));
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static
     */
    public function keys()
    {
        return new Collection(array_keys($this->items));
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
        return Arr::last($this->items, $callback, $default);
    }

    /**
     * Get the values of a given key.
     *
     * @param  string|array  $value
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function pluck($value, $key = null)
    {
        return new Collection(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Run a map over each of the items.
     *
     * @param  callable  $callback
     * @return \Illuminate\Support\Collection
     */
    public function map(callable $callback)
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new Collection(array_combine($keys, $items));
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

        foreach ($this->items as $key => $item) {
            $pair = $callback($item, $key);

            $key = key($pair);

            $value = reset($pair);

            if (! isset($dictionary[$key])) {
                $dictionary[$key] = [];
            }

            $dictionary[$key][] = $value;
        }

        return new Collection($dictionary);
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

        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);

            foreach ($assoc as $mapKey => $mapValue) {
                $result[$mapKey] = $mapValue;
            }
        }

        return new Collection($result);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function merge($items)
    {
        return static::subset(array_merge($this->items, static::resolveItems($items)));
    }

    /**
     * Union the collection with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function union($items)
    {
        return static::subset($this->items + static::resolveItems($items));
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

        foreach ($this->items as $key => $item) {
            if ($position % $step === $offset) {
                $new[$key] = $item;
            }

            $position++;
        }

        return static::subset($new);
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
            return static::subset($this->items);
        }

        if ($keys instanceof Enumerable) {
            $keys = $keys->all();
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return static::subset(Arr::only($this->items, $keys));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return mixed
     */
    public function pop()
    {
        return array_pop($this->items);
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
        foreach (static::resolveItems($value) as $key => $model) {
            $this->items = Arr::prepend($this->items, $model, $key);
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
        $this->items = array_merge($this->items, static::resolveItems($values));

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

        return static::subset(array_merge($this->items, static::resolveItems($args)));
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
        return Arr::pull($this->items, $key, $default);
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
            return Arr::random($this->items);
        }

        return static::subset(Arr::random($this->items, $number, true));
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
        return array_reduce($this->items, $callback, $initial);
    }

    /**
     * Replace the collection items with the given items.
     *
     * @param  mixed  $items
     * @return static
     */
    public function replace($items)
    {
        return static::subset(array_replace($this->items, static::resolveItems($items)));
    }

    /**
     * Reverse items order.
     *
     * @return static
     */
    public function reverse()
    {
        return static::subset(array_reverse($this->items, true));
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
            return array_search($value, $this->items, $strict);
        }

        foreach ($this->items as $key => $item) {
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
        return array_shift($this->items);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @param  int|null  $seed
     * @return static
     */
    public function shuffle($seed = null)
    {
        return static::subset(Arr::shuffle($this->items, $seed));
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
        return static::subset($this->lazy()->skipUntil($value)->all());
    }

    /**
     * Skip items in the collection while the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function skipWhile($value)
    {
        return static::subset($this->lazy()->skipWhile($value)->all());
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
        return static::subset(array_slice($this->items, $offset, $length, true));
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

        $groups = new Collection;

        $groupSize = floor($this->count() / $numberOfGroups);

        $remain = $this->count() % $numberOfGroups;

        $start = 0;

        for ($i = 0; $i < $numberOfGroups; $i++) {
            $size = $groupSize;

            if ($i < $remain) {
                $size++;
            }

            if ($size) {
                $groups->push(static::subset(array_slice($this->items, $start, $size, true)));

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

        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = static::subset($chunk);
        }

        return new Collection($chunks);
    }

    /**
     * Sort through each item with a callback.
     *
     * @param  callable|int|null  $callback
     * @return static
     */
    public function sort($callback = null)
    {
        $items = $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback);

        return static::subset($items);
    }

    /**
     * Sort items in descending order.
     *
     * @param  int  $options
     * @return static
     */
    public function sortDesc($options = SORT_REGULAR)
    {
        $items = $this->items;

        arsort($items, $options);

        return static::subset($items);
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
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options)
            : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return static::subset($results);
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
        $items = $this->items;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return static::subset($items);
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
            return static::subset(array_splice($this->items, $offset));
        }

        if ($replacement) {
            $replacement = static::resolveItems($replacement);
        }

        return static::subset(array_splice($this->items, $offset, $length, $replacement));
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
        return static::subset($this->lazy()->takeUntil($value)->all());
    }

    /**
     * Take items in the collection while the given condition is met.
     *
     * @param  mixed  $value
     * @return static
     */
    public function takeWhile($value)
    {
        return static::subset($this->lazy()->takeWhile($value)->all());
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return \Illuminate\Support\Collection
     */
    public function values()
    {
        return new Collection(array_values($this->items));
    }

    /**
     * Execute a callback over each item.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Determine if all items pass the given truth test.
     *
     * @param  string|callable  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return bool
     */
    public function every($key, $operator = null, $value = null)
    {
        if (func_num_args() === 1) {
            $callback = $this->valueRetriever($key);

            foreach ($this as $k => $v) {
                if (! $callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        return $this->every($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Count the number of items in the collection by a field or using a callback.
     *
     * @param  callable|string  $countBy
     * @return \Illuminate\Support\Collection
     */
    public function countBy($countBy = null)
    {
        return new Collection($this->lazy()->countBy($countBy)->all());
    }

    /**
     * Get the first item by the given key value pair.
     *
     * @param  string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return mixed
     */
    public function firstWhere($key, $operator = null, $value = null)
    {
        return $this->first($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty()
    {
        return ! $this->isEmpty();
    }

    /**
     * Get the min value of a given key.
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function min($callback = null)
    {
        $callback = $this->valueRetriever($callback);

        return $this->map(function ($value) use ($callback) {
            return $callback($value);
        })->filter(function ($value) {
            return ! is_null($value);
        })->reduce(function ($result, $value) {
            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * Get the max value of a given key.
     *
     * @param  callable|string|null  $callback
     * @return mixed
     */
    public function max($callback = null)
    {
        $callback = $this->valueRetriever($callback);

        return $this->filter(function ($value) {
            return ! is_null($value);
        })->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * Partition the collection into two arrays using the given callback or key.
     *
     * @param  callable|string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return \Illuminate\Support\Collection
     */
    public function partition($key, $operator = null, $value = null)
    {
        $passed = [];
        $failed = [];

        $callback = func_num_args() === 1
                ? $this->valueRetriever($key)
                : $this->operatorForWhere(...func_get_args());

        foreach ($this as $key => $item) {
            if ($callback($item, $key)) {
                $passed[$key] = $item;
            } else {
                $failed[$key] = $item;
            }
        }

        return new Collection([static::subset($passed), static::subset($failed)]);
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string  $key
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return static
     */
    public function where($key, $operator = null, $value = null)
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string  $key
     * @param  mixed  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereIn($key, $values, $strict = false)
    {
        $values = Arr::from($values);

        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param  string  $key
     * @param  mixed  $values
     * @param  bool  $strict
     * @return static
     */
    public function whereNotIn($key, $values, $strict = false)
    {
        $values = Arr::from($values);

        return $this->reject(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }

    /**
     * Filter items such that the value of the given key is between the given values.
     *
     * @param  string  $key
     * @param  array  $values
     * @return static
     */
    public function whereBetween($key, $values)
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }

    /**
     * Filter items such that the value of the given key is not between the given values.
     *
     * @param  string  $key
     * @param  array  $values
     * @return static
     */
    public function whereNotBetween($key, $values)
    {
        return $this->filter(function ($item) use ($key, $values) {
            return data_get($item, $key) < reset($values) || data_get($item, $key) > end($values);
        });
    }

    /**
     * Pass the collection to the given callback and return the result.
     *
     * @param  callable  $callback
     * @return mixed
     */
    public function pipe(callable $callback)
    {
        return $callback($this);
    }

    /**
     * Pass the collection to the given callback and then return it.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function tap(callable $callback)
    {
        $callback(clone $this);

        return $this;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param  callable|mixed  $callback
     * @return static
     */
    public function reject($callback = true)
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? ! $callback($value, $key)
                : $value != $callback;
        });
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
     * Make a function to check an item's equality.
     *
     * @param  mixed  $value
     * @return \Closure
     */
    protected function equality($value)
    {
        return function ($item) use ($value) {
            return $item === $value;
        };
    }

    /**
     * Make a function using another function, by negating its result.
     *
     * @param  \Closure  $callback
     * @return \Closure
     */
    protected function negate(Closure $callback)
    {
        return function (...$params) use ($callback) {
            return ! $callback(...$params);
        };
    }

    /**
     * Make a function that returns what's passed to it.
     *
     * @return \Closure
     */
    protected function identity()
    {
        return function ($value) {
            return $value;
        };
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->items[$key];
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
        if ($item = static::resolveItem($value)) {
            $key = $key ?? static::getItemKey($item);

            if (is_null($key)) {
                $this->items[] = $item;
            } else {
                $this->items[$key] = $item;
            }
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
        unset($this->items[$key]);
    }
}
