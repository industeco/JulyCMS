<?php

// namespace App\Support;

// use App\Support\Arr;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Enumerable;

// /**
//  * Set：只允许特定类型项目的特殊集合
//  */
// abstract class SetBase extends Collection
// {
//     const PROCESS_NONE = 0;
//     const PROCESS_COLLECT = 1;
//     const PROCESS_FILTER = 2;
//     const PROCESS_INDEX = 4;

//     /**
//      * The items contained in the collection.
//      *
//      * @var array
//      */
//     protected $items = [];

//     /**
//      * Create a new collection.
//      *
//      * @param  mixed  $items
//      * @param  int  $options
//      * @return void
//      */
//     public function __construct($items = [], $options = 1)
//     {
//         if (empty($items)) {
//             return;
//         }

//         $this->items = static::processItems($items, $options);
//     }

//     /**
//      * @param  mixed  $items
//      * @param  int $process = 0
//      * @return array
//      */
//     protected static function processItems($items, $process)
//     {
//         $items = Arr::from($items);

//         if (! $process) {
//             return $items;
//         }

//         if ($process & static::PROCESS_COLLECT) {
//             return static::resolveItems($items);
//         }

//         if ($process & static::PROCESS_FILTER) {
//             $items = static::filterItems($items);
//         }

//         if ($process & static::PROCESS_INDEX) {
//             $items = static::indexItems($items);
//         }

//         return $items;
//     }

//     /**
//      * Create a new collection instance if the value isn't one already.
//      *
//      * @param  mixed  $items
//      * @param  int  $options
//      * @return static
//      */
//     public static function make($items = [], $options = 1)
//     {
//         return new static($items, $options);
//     }

//     /**
//      * 使用当前集合的子集，或其它可信任项目，生成新的集合
//      *
//      * @param  mixed $items 当前集合的子集，或其它可信任项目的集合
//      * @param  int $options = 0
//      * @return static
//      */
//     public static function subset($items = [], $options = 0)
//     {
//         return new static($items, $options);
//     }

//     /**
//      * 验证是否可作为集合项
//      *
//      * @param  mixed $item
//      * @return bool
//      */
//     abstract public static function isValidItem($item);

//     /**
//      * 获取集合项的键
//      *
//      * @param  mixed $item
//      * @return string|int|null
//      */
//     public static function getItemKey($item)
//     {
//         return null;
//     }

//     /**
//      * 从给定数据中解析符合集合类型的项目
//      *
//      * @param  mixed $item
//      * @return mixed
//      */
//     public static function resolveItem($item)
//     {
//         return static::isValidItem($item) ? $item : null;
//     }

//     /**
//      * 从给定数据中解析符合集合类型的项目
//      *
//      * @param  mixed $items
//      * @return array
//      */
//     public static function resolveItems($items = [])
//     {
//         $results = [];

//         foreach (Arr::from($items) as $item) {
//             if ($item = static::resolveItem($item)) {
//                 $results[] = $item;
//             }
//         }

//         return static::indexItems($results);
//     }

//     /**
//      * 过滤掉不符合集合类型的项目
//      *
//      * @param  array $items
//      * @return array
//      */
//     public static function filterItems(array $items)
//     {
//         return array_filter($items, function($item) {
//             return static::isValidItem($item);
//         });
//     }

//     /**
//      * 索引集合项目
//      *
//      * @param  array $items
//      * @return array
//      */
//     public static function indexItems(array $items)
//     {
//         $keys = [];
//         foreach ($items as $index => $item) {
//             $keys[] = static::getItemKey($item) ?? $index;
//         }

//         return array_combine($keys, $items);
//     }


//     //////////////////////////////////////////////////////


//     /**
//      * Create a new collection by invoking the callback a given amount of times.
//      *
//      * @param  int  $number
//      * @param  callable|null  $callback
//      * @return \Illuminate\Support\Collection
//      */
//     public static function times($number, callable $callback = null)
//     {
//         throw new \BadMethodCallException("该方法在当前集合中无意义", 1);
//     }

//     /**
//      * Get the mode of a given key.
//      *
//      * @param  string|array|null  $key
//      * @return array|null
//      */
//     public function mode($key = null)
//     {
//         throw new \BadMethodCallException("该方法在当前集合中无意义", 1);
//     }

//     /**
//      * Collapse the collection of items into a single array.
//      *
//      * @return static
//      */
//     public function collapse()
//     {
//         return $this;
//     }

//     /**
//      * Cross join with the given lists, returning all possible permutations.
//      *
//      * @param  mixed  ...$lists
//      * @return static
//      */
//     public function crossJoin(...$lists)
//     {
//         throw new \BadMethodCallException("该方法在当前集合中无意义", 1);
//     }

//     /**
//      * Get the items in the collection that are not present in the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function diff($items)
//     {
//         return static::subset(array_diff($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Get the items in the collection that are not present in the given items, using the callback.
//      *
//      * @param  mixed  $items
//      * @param  callable  $callback
//      * @return static
//      */
//     public function diffUsing($items, callable $callback)
//     {
//         return static::subset(array_udiff($this->items, static::resolveItems($items), $callback));
//     }

//     /**
//      * Get the items in the collection whose keys and values are not present in the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function diffAssoc($items)
//     {
//         return static::subset(array_diff_assoc($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Get the items in the collection whose keys and values are not present in the given items, using the callback.
//      *
//      * @param  mixed  $items
//      * @param  callable  $callback
//      * @return static
//      */
//     public function diffAssocUsing($items, callable $callback)
//     {
//         return static::subset(array_diff_uassoc($this->items, static::resolveItems($items), $callback));
//     }

//     /**
//      * Get the items in the collection whose keys are not present in the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function diffKeys($items)
//     {
//         return static::subset(array_diff_key($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Get the items in the collection whose keys are not present in the given items, using the callback.
//      *
//      * @param  mixed  $items
//      * @param  callable  $callback
//      * @return static
//      */
//     public function diffKeysUsing($items, callable $callback)
//     {
//         return static::subset(array_diff_ukey($this->items, static::resolveItems($items), $callback));
//     }

//     /**
//      * Retrieve duplicate items from the collection.
//      *
//      * @param  callable|null  $callback
//      * @param  bool  $strict
//      * @return static
//      */
//     public function duplicates($callback = null, $strict = false)
//     {
//         $items = $this->map($this->valueRetriever($callback));

//         $uniqueItems = $items->unique(null, $strict);

//         $compare = $this->duplicateComparator($strict);

//         $duplicates = [];

//         foreach ($items as $key => $value) {
//             if ($uniqueItems->isNotEmpty() && $compare($value, $uniqueItems->first())) {
//                 $uniqueItems->shift();
//             } else {
//                 $duplicates[$key] = $value;
//             }
//         }

//         return static::subset($duplicates);
//     }

//     /**
//      * Get all items except for those with the specified keys.
//      *
//      * @param  \Illuminate\Support\Collection|mixed  $keys
//      * @return static
//      */
//     public function except($keys)
//     {
//         if ($keys instanceof Enumerable) {
//             $keys = $keys->all();
//         } elseif (! is_array($keys)) {
//             $keys = func_get_args();
//         }

//         return static::subset(Arr::except($this->items, $keys));
//     }

//     /**
//      * Run a filter over each of the items.
//      *
//      * @param  callable|null  $callback
//      * @return static
//      */
//     public function filter(callable $callback = null)
//     {
//         if ($callback) {
//             return static::subset(Arr::where($this->items, $callback));
//         }

//         return static::subset(array_filter($this->items));
//     }

//     /**
//      * Get a flattened array of the items in the collection.
//      *
//      * @param  int  $depth
//      * @return static
//      */
//     public function flatten($depth = INF)
//     {
//         return $this;
//     }

//     /**
//      * Flip the items in the collection.
//      *
//      * @return static
//      */
//     public function flip()
//     {
//         throw new \BadMethodCallException("该方法在当前集合中无意义", 1);
//     }

//     /**
//      * Group an associative array by a field or using a callback.
//      *
//      * @param  callable|string  $groupBy
//      * @param  bool  $preserveKeys
//      * @return \Illuminate\Support\Collection
//      */
//     public function groupBy($groupBy, $preserveKeys = true)
//     {
//         $groupBy = $this->valueRetriever($groupBy);

//         $results = [];

//         foreach ($this->items as $key => $value) {
//             $groupKeys = $groupBy($value, $key);

//             if (! is_array($groupKeys)) {
//                 $groupKeys = [$groupKeys];
//             }

//             foreach ($groupKeys as $groupKey) {
//                 $groupKey = is_bool($groupKey) ? (int) $groupKey : $groupKey;

//                 if (! array_key_exists($groupKey, $results)) {
//                     $results[$groupKey] = [];
//                 }

//                 $results[$groupKey][$key] = $value;
//             }
//         }

//         foreach ($results as $group => $items) {
//             $results[$group] = static::subset($items);
//         }

//         return new Collection($results);
//     }

//     /**
//      * Key an associative array by a field or using a callback.
//      *
//      * @param  callable|string  $keyBy
//      * @return static
//      */
//     public function keyBy($keyBy)
//     {
//         $keyBy = $this->valueRetriever($keyBy);

//         $results = [];

//         foreach ($this->items as $key => $item) {
//             $resolvedKey = $keyBy($item, $key);

//             if (is_object($resolvedKey)) {
//                 $resolvedKey = (string) $resolvedKey;
//             }

//             $results[$resolvedKey] = $item;
//         }

//         return new Collection($results);
//     }

//     /**
//      * Intersect the collection with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function intersect($items)
//     {
//         return static::subset(array_intersect($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Intersect the collection with the given items by key.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function intersectByKeys($items)
//     {
//         return static::subset(array_intersect_key(
//             $this->items, static::resolveItems($items)
//         ));
//     }

//     /**
//      * Join all items from the collection using a string. The final items can use a separate glue string.
//      *
//      * @param  string  $glue
//      * @param  string  $finalGlue
//      * @return string
//      */
//     public function join($glue, $finalGlue = '')
//     {
//         if ($finalGlue === '') {
//             return $this->implode($glue);
//         }

//         $count = $this->count();

//         if ($count === 0) {
//             return '';
//         }

//         if ($count === 1) {
//             return $this->last();
//         }

//         $collection = new Collection($this->items);

//         $finalItem = $collection->pop();

//         return $collection->implode($glue).$finalGlue.$finalItem;
//     }

//     /**
//      * Get the keys of the collection items.
//      *
//      * @return \Illuminate\Support\Collection
//      */
//     public function keys()
//     {
//         return new Collection(array_keys($this->items));
//     }

//     /**
//      * Get the values of a given key.
//      *
//      * @param  string|array  $value
//      * @param  string|null  $key
//      * @return \Illuminate\Support\Collection
//      */
//     public function pluck($value, $key = null)
//     {
//         return new Collection(Arr::pluck($this->items, $value, $key));
//     }

//     /**
//      * Run a map over each of the items.
//      *
//      * @param  callable  $callback
//      * @return \Illuminate\Support\Collection
//      */
//     public function map(callable $callback)
//     {
//         $keys = array_keys($this->items);

//         $items = array_map($callback, $this->items, $keys);

//         return new Collection(array_combine($keys, $items));
//     }

//     /**
//      * Run a dictionary map over the items.
//      *
//      * The callback should return an associative array with a single key/value pair.
//      *
//      * @param  callable  $callback
//      * @return \Illuminate\Support\Collection
//      */
//     public function mapToDictionary(callable $callback)
//     {
//         $dictionary = [];

//         foreach ($this->items as $key => $item) {
//             $pair = $callback($item, $key);

//             $key = key($pair);

//             $value = reset($pair);

//             if (! isset($dictionary[$key])) {
//                 $dictionary[$key] = [];
//             }

//             $dictionary[$key][] = $value;
//         }

//         return new Collection($dictionary);
//     }

//     /**
//      * Run an associative map over each of the items.
//      *
//      * The callback should return an associative array with a single key/value pair.
//      *
//      * @param  callable  $callback
//      * @return \Illuminate\Support\Collection
//      */
//     public function mapWithKeys(callable $callback)
//     {
//         $result = [];

//         foreach ($this->items as $key => $value) {
//             $assoc = $callback($value, $key);

//             foreach ($assoc as $mapKey => $mapValue) {
//                 $result[$mapKey] = $mapValue;
//             }
//         }

//         return new Collection($result);
//     }

//     /**
//      * Merge the collection with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function merge($items)
//     {
//         return static::subset(array_merge($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Recursively merge the collection with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function mergeRecursive($items)
//     {
//         return static::subset(array_merge($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Create a collection by using this collection for keys and another for its values.
//      *
//      * @param  mixed  $values
//      * @return \Illuminate\Support\Collection
//      */
//     public function combine($values)
//     {
//         return new Collection(array_combine(array_keys($this->items), Arr::from($values)));
//     }

//     /**
//      * Union the collection with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function union($items)
//     {
//         return static::subset($this->items + static::resolveItems($items));
//     }

//     /**
//      * Create a new collection consisting of every n-th element.
//      *
//      * @param  int  $step
//      * @param  int  $offset
//      * @return static
//      */
//     public function nth($step, $offset = 0)
//     {
//         $new = [];

//         $position = 0;

//         foreach ($this->items as $key => $item) {
//             if ($position % $step === $offset) {
//                 $new[$key] = $item;
//             }

//             $position++;
//         }

//         return static::subset($new);
//     }

//     /**
//      * Get the items with the specified keys.
//      *
//      * @param  mixed  $keys
//      * @return static
//      */
//     public function only($keys)
//     {
//         if (is_null($keys)) {
//             return static::subset($this->items);
//         }

//         if ($keys instanceof Enumerable) {
//             $keys = $keys->all();
//         }

//         $keys = is_array($keys) ? $keys : func_get_args();

//         return static::subset(Arr::only($this->items, $keys));
//     }

//     /**
//      * Push an item onto the beginning of the collection.
//      *
//      * @param  mixed  $value
//      * @param  mixed  $key
//      * @return $this
//      */
//     public function prepend($value, $key = null)
//     {
//         $item = static::resolveItem($value);

//         $this->items = Arr::prepend($this->items, $item, $key ?? static::getItemKey($item));

//         return $this;
//     }

//     /**
//      * Push one or more items onto the end of the collection.
//      *
//      * @param  mixed  $values [optional]
//      * @return $this
//      */
//     public function push(...$values)
//     {
//         $this->items = array_merge($this->items, static::resolveItems($values));

//         return $this;
//     }

//     /**
//      * Push all of the given items onto the collection.
//      *
//      * @param  iterable  $source
//      * @return static
//      */
//     public function concat($source)
//     {
//         $args = [];
//         foreach ($source as $item) {
//             $args[] = $item;
//         }

//         return static::subset(array_merge($this->items, static::resolveItems($args)));
//     }

//     /**
//      * Get one or a specified number of items randomly from the collection.
//      *
//      * @param  int|null  $number
//      * @return static|mixed
//      *
//      * @throws \InvalidArgumentException
//      */
//     public function random($number = null)
//     {
//         if (is_null($number)) {
//             return Arr::random($this->items);
//         }

//         return static::subset(Arr::random($this->items, $number, true));
//     }

//     /**
//      * Replace the collection items with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function replace($items)
//     {
//         return static::subset(array_replace($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Recursively replace the collection items with the given items.
//      *
//      * @param  mixed  $items
//      * @return static
//      */
//     public function replaceRecursive($items)
//     {
//         return static::subset(array_replace($this->items, static::resolveItems($items)));
//     }

//     /**
//      * Reverse items order.
//      *
//      * @return static
//      */
//     public function reverse()
//     {
//         return static::subset(array_reverse($this->items, true));
//     }

//     /**
//      * Shuffle the items in the collection.
//      *
//      * @param  int|null  $seed
//      * @return static
//      */
//     public function shuffle($seed = null)
//     {
//         return static::subset(Arr::shuffle($this->items, $seed));
//     }

//     /**
//      * Skip items in the collection until the given condition is met.
//      *
//      * @param  mixed  $value
//      * @return static
//      */
//     public function skipUntil($value)
//     {
//         return static::subset($this->lazy()->skipUntil($value)->all());
//     }

//     /**
//      * Skip items in the collection while the given condition is met.
//      *
//      * @param  mixed  $value
//      * @return static
//      */
//     public function skipWhile($value)
//     {
//         return static::subset($this->lazy()->skipWhile($value)->all());
//     }

//     /**
//      * Slice the underlying collection array.
//      *
//      * @param  int  $offset
//      * @param  int|null  $length
//      * @return static
//      */
//     public function slice($offset, $length = null)
//     {
//         return static::subset(array_slice($this->items, $offset, $length, true));
//     }

//     /**
//      * Split a collection into a certain number of groups.
//      *
//      * @param  int  $numberOfGroups
//      * @return static
//      */
//     public function split($numberOfGroups)
//     {
//         if ($this->isEmpty()) {
//             return new static;
//         }

//         $groups = new Collection;

//         $groupSize = floor($this->count() / $numberOfGroups);

//         $remain = $this->count() % $numberOfGroups;

//         $start = 0;

//         for ($i = 0; $i < $numberOfGroups; $i++) {
//             $size = $groupSize;

//             if ($i < $remain) {
//                 $size++;
//             }

//             if ($size) {
//                 $groups->push(static::subset(array_slice($this->items, $start, $size, true)));

//                 $start += $size;
//             }
//         }

//         return $groups;
//     }

//     /**
//      * Chunk the collection into chunks of the given size.
//      *
//      * @param  int  $size
//      * @return \Illuminate\Support\Collection
//      */
//     public function chunk($size)
//     {
//         if ($size <= 0) {
//             return new static;
//         }

//         $chunks = [];

//         foreach (array_chunk($this->items, $size, true) as $chunk) {
//             $chunks[] = static::subset($chunk);
//         }

//         return new Collection($chunks);
//     }

//     /**
//      * Sort through each item with a callback.
//      *
//      * @param  callable|int|null  $callback
//      * @return static
//      */
//     public function sort($callback = null)
//     {
//         $items = $this->items;

//         $callback && is_callable($callback)
//             ? uasort($items, $callback)
//             : asort($items, $callback);

//         return static::subset($items);
//     }

//     /**
//      * Sort items in descending order.
//      *
//      * @param  int  $options
//      * @return static
//      */
//     public function sortDesc($options = SORT_REGULAR)
//     {
//         $items = $this->items;

//         arsort($items, $options);

//         return static::subset($items);
//     }

//     /**
//      * Sort the collection using the given callback.
//      *
//      * @param  callable|string  $callback
//      * @param  int  $options
//      * @param  bool  $descending
//      * @return static
//      */
//     public function sortBy($callback, $options = SORT_REGULAR, $descending = false)
//     {
//         $results = [];

//         $callback = $this->valueRetriever($callback);

//         // First we will loop through the items and get the comparator from a callback
//         // function which we were given. Then, we will sort the returned values and
//         // and grab the corresponding values for the sorted keys from this array.
//         foreach ($this->items as $key => $value) {
//             $results[$key] = $callback($value, $key);
//         }

//         $descending ? arsort($results, $options)
//             : asort($results, $options);

//         // Once we have sorted all of the keys in the array, we will loop through them
//         // and grab the corresponding model so we can set the underlying items list
//         // to the sorted version. Then we'll just return the collection instance.
//         foreach (array_keys($results) as $key) {
//             $results[$key] = $this->items[$key];
//         }

//         return static::subset($results);
//     }

//     /**
//      * Sort the collection keys.
//      *
//      * @param  int  $options
//      * @param  bool  $descending
//      * @return static
//      */
//     public function sortKeys($options = SORT_REGULAR, $descending = false)
//     {
//         $items = $this->items;

//         $descending ? krsort($items, $options) : ksort($items, $options);

//         return static::subset($items);
//     }

//     /**
//      * Splice a portion of the underlying collection array.
//      *
//      * @param  int  $offset
//      * @param  int|null  $length
//      * @param  mixed  $replacement
//      * @return static
//      */
//     public function splice($offset, $length = null, $replacement = [])
//     {
//         if (func_num_args() === 1) {
//             return static::subset(array_splice($this->items, $offset));
//         }

//         if ($replacement) {
//             $replacement = static::resolveItems($replacement);
//         }

//         return static::subset(array_splice($this->items, $offset, $length, $replacement));
//     }

//     /**
//      * Take items in the collection until the given condition is met.
//      *
//      * @param  mixed  $value
//      * @return static
//      */
//     public function takeUntil($value)
//     {
//         return static::subset($this->lazy()->takeUntil($value)->all());
//     }

//     /**
//      * Take items in the collection while the given condition is met.
//      *
//      * @param  mixed  $value
//      * @return static
//      */
//     public function takeWhile($value)
//     {
//         return static::subset($this->lazy()->takeWhile($value)->all());
//     }

//     /**
//      * Reset the keys on the underlying array.
//      *
//      * @return \Illuminate\Support\Collection
//      */
//     public function values()
//     {
//         return new Collection(array_values($this->items));
//     }

//     /**
//      * Zip the collection together with one or more arrays.
//      *
//      * e.g. new Collection([1, 2, 3])->zip([4, 5, 6]);
//      *      => [[1, 4], [2, 5], [3, 6]]
//      *
//      * @param  mixed  ...$items
//      * @return \Illuminate\Support\Collection
//      */
//     public function zip($items)
//     {
//         $arrayableItems = array_map(function ($items) {
//             return $this->getArrayableItems($items);
//         }, func_get_args());

//         $params = array_merge([function () {
//             return new Collection(func_get_args());
//         }, $this->items], $arrayableItems);

//         return new Collection(array_map(...$params));
//     }

//     /**
//      * Pad collection to the specified length with a value.
//      *
//      * @param  int  $size
//      * @param  mixed  $value
//      * @return \Illuminate\Support\Collection
//      */
//     public function pad($size, $value)
//     {
//         return new Collection(array_pad($this->items, $size, $value));
//     }

//     /**
//      * Count the number of items in the collection by a field or using a callback.
//      *
//      * @param  callable|string  $countBy
//      * @return \Illuminate\Support\Collection
//      */
//     public function countBy($countBy = null)
//     {
//         return new Collection($this->lazy()->countBy($countBy)->all());
//     }

//     /**
//      * Add an item to the collection.
//      *
//      * @param  mixed  $item
//      * @return $this
//      */
//     public function add($item)
//     {
//         $item = static::resolveItem($item);
//         if ($key = static::getItemKey($item)) {
//             $this->items[$key] = $item;
//         } else {
//             $this->items[] = $item;
//         }

//         return $this;
//     }

//     /**
//      * Wrap the given value in a collection if applicable.
//      *
//      * @param  mixed  $value
//      * @return static|\Illuminate\Support\Collection
//      */
//     public static function wrap($value)
//     {
//         return $value instanceof static
//             ? $value
//             : new Collection(Arr::wrap($value));
//     }

//     /**
//      * Partition the collection into two arrays using the given callback or key.
//      *
//      * @param  callable|string  $key
//      * @param  mixed  $operator
//      * @param  mixed  $value
//      * @return \Illuminate\Support\Collection
//      */
//     public function partition($key, $operator = null, $value = null)
//     {
//         $passed = [];
//         $failed = [];

//         $callback = func_num_args() === 1
//                 ? $this->valueRetriever($key)
//                 : $this->operatorForWhere(...func_get_args());

//         foreach ($this as $key => $item) {
//             if ($callback($item, $key)) {
//                 $passed[$key] = $item;
//             } else {
//                 $failed[$key] = $item;
//             }
//         }

//         return new Collection([static::subset($passed), static::subset($failed)]);
//     }

//     /**
//      * Set the item at a given offset.
//      *
//      * @param  mixed  $key
//      * @param  mixed  $value
//      * @return void
//      */
//     public function offsetSet($key, $value)
//     {
//         if ($item = static::resolveItem($value)) {
//             $key = $key ?? static::getItemKey($item);

//             if (is_null($key)) {
//                 $this->items[] = $item;
//             } else {
//                 $this->items[$key] = $item;
//             }
//         }
//     }
// }
