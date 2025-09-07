<?php

declare(strict_types=1);

namespace Lythany\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Lythany\Support\Traits\Macroable;
use stdClass;
use Traversable;

/**
 * Collection provides a fluent interface for working with arrays of data.
 * 
 * This class offers a wide range of methods for filtering, mapping, reducing,
 * and manipulating collections of data in a chainable, expressive way.
 * 
 * @template TKey of array-key
 * @template TValue
 * 
 * @implements ArrayAccess<TKey, TValue>
 * @implements IteratorAggregate<TKey, TValue>
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    use Macroable;

    /**
     * The items contained in the collection.
     *
     * @var array<TKey, TValue>
     */
    protected array $items = [];

    /**
     * Create a new collection instance.
     *
     * @param iterable<TKey, TValue> $items
     */
    public function __construct(iterable $items = [])
    {
        $this->items = is_array($items) ? $items : iterator_to_array($items);
    }

    /**
     * Create a new collection instance from the given items.
     *
     * @template TMakeKey of array-key
     * @template TMakeValue
     * @param iterable<TMakeKey, TMakeValue> $items
     * @return static<TMakeKey, TMakeValue>
     */
    public static function make(iterable $items = []): static
    {
        return new static($items);
    }

    /**
     * Create a collection with a range of numbers.
     *
     * @param int $from
     * @param int $to
     * @param int $step
     * @return static<int, int>
     */
    public static function range(int $from, int $to, int $step = 1): static
    {
        return new static(range($from, $to, $step));
    }

    /**
     * Create a collection of times by calling the given callback.
     *
     * @template TTimesValue
     * @param int $number
     * @param callable(int): TTimesValue $callback
     * @return static<int, TTimesValue>
     */
    public static function times(int $number, callable $callback): static
    {
        if ($number < 1) {
            return new static();
        }

        return new static(array_map($callback, range(1, $number)));
    }

    /**
     * Get all items in the collection.
     *
     * @return array<TKey, TValue>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Get the average value of a given key.
     *
     * @param (callable(TValue, TKey): float|int)|string|null $callback
     * @return float|int|null
     */
    public function avg(callable|string|null $callback = null): float|int|null
    {
        $callback = $this->valueRetriever($callback);

        $items = $this->map($callback)->filter(fn($value) => !is_null($value));

        if ($count = $items->count()) {
            return $items->sum() / $count;
        }

        return null;
    }

    /**
     * Alias for the "avg" method.
     *
     * @param (callable(TValue, TKey): float|int)|string|null $callback
     * @return float|int|null
     */
    public function average(callable|string|null $callback = null): float|int|null
    {
        return $this->avg($callback);
    }

    /**
     * Break the collection into multiple, smaller collections of a given size.
     *
     * @param int $size
     * @return static<int, static<TKey, TValue>>
     */
    public function chunk(int $size): static
    {
        if ($size <= 0) {
            return new static();
        }

        $chunks = [];
        foreach (array_chunk($this->items, $size, true) as $chunk) {
            $chunks[] = new static($chunk);
        }

        return new static($chunks);
    }

    /**
     * Collapse the collection of items into a single, flat collection.
     *
     * @return static<array-key, mixed>
     */
    public function collapse(): static
    {
        $results = [];

        foreach ($this->items as $values) {
            if ($values instanceof self) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results = array_merge($results, $values);
        }

        return new static($results);
    }

    /**
     * Determine if an item exists in the collection.
     *
     * @param (callable(TValue, TKey): bool)|TValue|string $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function contains(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            if (is_callable($key)) {
                $placeholder = new stdClass;
                return $this->first($key, $placeholder) !== $placeholder;
            }

            return in_array($key, $this->items, true);
        }

        return $this->contains($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Count the occurrences of each item in the collection.
     *
     * @return static<TValue, int>
     */
    public function countBy(?callable $callback = null): static
    {
        return new static($this->groupBy($callback)->map(fn($value) => $value->count()));
    }

    /**
     * Get the items that are not present in the given items.
     *
     * @param iterable<array-key, TValue> $items
     * @return static<TKey, TValue>
     */
    public function diff(iterable $items): static
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the items that are not present in the given items, using the callback.
     *
     * @param iterable<array-key, TValue> $items
     * @param callable(TValue, TValue): int $callback
     * @return static<TKey, TValue>
     */
    public function diffUsing(iterable $items, callable $callback): static
    {
        return new static(array_udiff($this->items, $this->getArrayableItems($items), $callback));
    }

    /**
     * Get the items whose keys and values are not present in the given items.
     *
     * @param iterable<TKey, TValue> $items
     * @return static<TKey, TValue>
     */
    public function diffAssoc(iterable $items): static
    {
        return new static(array_diff_assoc($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the items whose keys are not present in the given items.
     *
     * @param iterable<TKey, mixed> $items
     * @return static<TKey, TValue>
     */
    public function diffKeys(iterable $items): static
    {
        return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Execute a callback over each item.
     *
     * @param callable(TValue, TKey): mixed $callback
     * @return $this
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Determine if all items pass the given test.
     *
     * @param (callable(TValue, TKey): bool)|TValue|string $key
     * @param mixed $operator
     * @param mixed $value
     * @return bool
     */
    public function every(mixed $key, mixed $operator = null, mixed $value = null): bool
    {
        if (func_num_args() === 1) {
            $callback = $this->valueRetriever($key);

            foreach ($this->items as $k => $v) {
                if (!$callback($v, $k)) {
                    return false;
                }
            }

            return true;
        }

        return $this->every($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Get all items except for those with the specified keys.
     *
     * @param array<array-key, TKey>|TKey $keys
     * @return static<TKey, TValue>
     */
    public function except(mixed $keys): static
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        if (is_callable($keys)) {
            return $this->reject($keys);
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::except($this->items, $keys));
    }

    /**
     * Run a filter over each of the items.
     *
     * @param (callable(TValue, TKey): bool)|null $callback
     * @return static<TKey, TValue>
     */
    public function filter(?callable $callback = null): static
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Get the first item from the collection passing the given truth test.
     *
     * @template TFirstDefault
     * @param (callable(TValue, TKey): bool)|null $callback
     * @param TFirstDefault $default
     * @return TValue|TFirstDefault
     */
    public function first(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }

            foreach ($this->items as $item) {
                return $item;
            }
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param int $depth
     * @return static<array-key, mixed>
     */
    public function flatten(int $depth = PHP_INT_MAX): static
    {
        return new static(Arr::flatten($this->items, $depth));
    }

    /**
     * Flip the items in the collection.
     *
     * @return static<TValue, TKey>
     */
    public function flip(): static
    {
        return new static(array_flip($this->items));
    }

    /**
     * Remove an item from the collection by key.
     *
     * @param array<array-key, TKey>|TKey $keys
     * @return $this
     */
    public function forget(mixed $keys): static
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Get an item from the collection by key.
     *
     * @template TGetDefault
     * @param TKey $key
     * @param TGetDefault $default
     * @return TValue|TGetDefault
     */
    public function get(mixed $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Group an associative array by a field or using a callback.
     *
     * @param (callable(TValue, TKey): array-key)|array-key|string $groupBy
     * @param bool $preserveKeys
     * @return static<array-key, static<array-key, TValue>>
     */
    public function groupBy(mixed $groupBy, bool $preserveKeys = false): static
    {
        if (!$this->useAsCallable($groupBy) && is_array($groupBy)) {
            $nextGroups = $groupBy;

            $groupBy = array_shift($nextGroups);
        }

        $groupBy = $this->valueRetriever($groupBy);

        $results = [];

        foreach ($this->items as $key => $value) {
            $groupKeys = $groupBy($value, $key);

            if (!is_array($groupKeys)) {
                $groupKeys = [$groupKeys];
            }

            foreach ($groupKeys as $groupKey) {
                $groupKey = is_bool($groupKey) ? (int) $groupKey : $groupKey;

                if (!array_key_exists($groupKey, $results)) {
                    $results[$groupKey] = new static;
                }

                $results[$groupKey]->offsetSet($preserveKeys ? $key : null, $value);
            }
        }

        $result = new static($results);

        if (!empty($nextGroups)) {
            return $result->map(fn($group) => $group->groupBy($nextGroups, $preserveKeys));
        }

        return $result;
    }

    /**
     * Determine if an item exists in the collection by key.
     *
     * @param TKey|array<array-key, TKey> $key
     * @return bool
     */
    public function has(mixed $key): bool
    {
        $keys = is_array($key) ? $key : func_get_args();

        foreach ($keys as $value) {
            if (!$this->offsetExists($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Concatenate values of a given key as a string.
     *
     * @param callable|string $value
     * @param string|null $glue
     * @return string
     */
    public function implode(mixed $value, ?string $glue = null): string
    {
        $first = $this->first();

        if (is_array($first) || (is_object($first) && !$first instanceof \Stringable)) {
            return implode($glue ?? '', $this->pluck($value)->all());
        }

        return implode($value ?? '', $this->items);
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param iterable<array-key, TValue> $items
     * @return static<TKey, TValue>
     */
    public function intersect(iterable $items): static
    {
        return new static(array_intersect($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Intersect the collection with the given items by key.
     *
     * @param iterable<TKey, mixed> $items
     * @return static<TKey, TValue>
     */
    public function intersectByKeys(iterable $items): static
    {
        return new static(array_intersect_key($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Determine if the collection is empty or not.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Determine if the collection is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    /**
     * Join all items from the collection using a string.
     *
     * @param string $glue
     * @param string $finalGlue
     * @return string
     */
    public function join(string $glue, string $finalGlue = ''): string
    {
        if ($finalGlue === '') {
            return $this->implode($glue);
        }

        $count = $this->count();

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return (string) $this->last();
        }

        $collection = new static($this->items);

        $finalItem = $collection->pop();

        return $collection->implode($glue) . $finalGlue . $finalItem;
    }

    /**
     * Get the keys of the collection items.
     *
     * @return static<int, TKey>
     */
    public function keys(): static
    {
        return new static(array_keys($this->items));
    }

    /**
     * Get the last item from the collection.
     *
     * @template TLastDefault
     * @param (callable(TValue, TKey): bool)|null $callback
     * @param TLastDefault $default
     * @return TValue|TLastDefault
     */
    public function last(?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($this->items) ? $default : end($this->items);
        }

        return $this->filter($callback)->last() ?? $default;
    }

    /**
     * Run a map over each of the items.
     *
     * @template TMapValue
     * @param callable(TValue, TKey): TMapValue $callback
     * @return static<TKey, TMapValue>
     */
    public function map(callable $callback): static
    {
        $keys = array_keys($this->items);

        $items = array_map($callback, $this->items, $keys);

        return new static(array_combine($keys, $items));
    }

    /**
     * Run a map over each nested chunk of items.
     *
     * @template TMapValue
     * @param callable(TValue): TMapValue $callback
     * @return static<TKey, mixed>
     */
    public function mapSpread(callable $callback): static
    {
        return $this->map(function ($chunk, $key) use ($callback) {
            $chunk[] = $key;

            return $callback(...$chunk);
        });
    }

    /**
     * Run a map over each of the items and flatten the result.
     *
     * @template TMapValue
     * @param callable(TValue, TKey): iterable<TMapValue> $callback
     * @return static<array-key, TMapValue>
     */
    public function flatMap(callable $callback): static
    {
        return $this->map($callback)->collapse();
    }

    /**
     * Map a collection and remove falsy values.
     *
     * @template TMapValue
     * @param callable(TValue, TKey): array<array-key, TMapValue> $callback
     * @return static<array-key, TMapValue>
     */
    public function mapWithKeys(callable $callback): static
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $assoc = $callback($value, $key);

            if (is_array($assoc)) {
                foreach ($assoc as $mapKey => $mapValue) {
                    $result[$mapKey] = $mapValue;
                }
            }
        }

        return new static($result);
    }

    /**
     * Get the max value of a given key.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $callback
     * @return mixed
     */
    public function max(?callable $callback = null): mixed
    {
        $callback = $this->valueRetriever($callback);

        return $this->filter(fn($value) => !is_null($value))->reduce(function ($result, $item) use ($callback) {
            $value = $callback($item);

            return is_null($result) || $value > $result ? $value : $result;
        });
    }

    /**
     * Merge the collection with the given items.
     *
     * @param iterable<array-key, TValue> $items
     * @return static<array-key, TValue>
     */
    public function merge(iterable $items): static
    {
        return new static(array_merge($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Recursively merge the collection with the given items.
     *
     * @param iterable<array-key, TValue> $items
     * @return static<array-key, TValue>
     */
    public function mergeRecursive(iterable $items): static
    {
        return new static(array_merge_recursive($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the min value of a given key.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $callback
     * @return mixed
     */
    public function min(?callable $callback = null): mixed
    {
        $callback = $this->valueRetriever($callback);

        return $this->map($callback)->filter(fn($value) => !is_null($value))->reduce(function ($result, $value) {
            return is_null($result) || $value < $result ? $value : $result;
        });
    }

    /**
     * Create a new collection consisting of every n-th element.
     *
     * @param int $step
     * @param int $offset
     * @return static<TKey, TValue>
     */
    public function nth(int $step, int $offset = 0): static
    {
        $new = [];

        $position = 0;

        foreach ($this->items as $key => $item) {
            if ($position % $step === $offset) {
                $new[$key] = $item;
            }

            $position++;
        }

        return new static($new);
    }

    /**
     * Get the items with the specified keys.
     *
     * @param iterable<array-key, TKey>|TKey|null $keys
     * @return static<TKey, TValue>
     */
    public function only(mixed $keys): static
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        $keys = is_array($keys) ? $keys : func_get_args();

        return new static(Arr::only($this->items, $keys));
    }

    /**
     * Get the values of a given key.
     *
     * @param string|array<array-key, string>|null $value
     * @param string|null $key
     * @return static<array-key, mixed>
     */
    public function pluck(mixed $value, ?string $key = null): static
    {
        return new static(Arr::pluck($this->items, $value, $key));
    }

    /**
     * Get and remove the last item from the collection.
     *
     * @return TValue|null
     */
    public function pop(): mixed
    {
        return array_pop($this->items);
    }

    /**
     * Push an item onto the beginning of the collection.
     *
     * @param TValue $value
     * @param TKey $key
     * @return $this
     */
    public function prepend(mixed $value, mixed $key = null): static
    {
        $this->items = Arr::prepend($this->items, $value, $key);

        return $this;
    }

    /**
     * Push one or more items onto the end of the collection.
     *
     * @param TValue ...$values
     * @return $this
     */
    public function push(mixed ...$values): static
    {
        foreach ($values as $value) {
            $this->items[] = $value;
        }

        return $this;
    }

    /**
     * Put an item in the collection by key.
     *
     * @param TKey $key
     * @param TValue $value
     * @return $this
     */
    public function put(mixed $key, mixed $value): static
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * Get one or a specified number of items randomly from the collection.
     *
     * @param int|null $number
     * @return static<array-key, TValue>|TValue
     */
    public function random(?int $number = null): mixed
    {
        if (is_null($number)) {
            if (empty($this->items)) {
                return null;
            }

            $keys = array_keys($this->items);
            $key = $keys[array_rand($keys)];
            
            return $this->items[$key];
        }

        if ($number > count($this->items)) {
            $number = count($this->items);
        }

        $keys = array_rand($this->items, $number);
        
        if ($number === 1) {
            $keys = [$keys];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[] = $this->items[$key];
        }

        return new static($result);
    }

    /**
     * Reduce the collection to a single value.
     *
     * @template TReduceInitial
     * @template TReduceReturnType
     * @param callable(TReduceInitial|TReduceReturnType, TValue, TKey): TReduceReturnType $callback
     * @param TReduceInitial $initial
     * @return TReduceInitial|TReduceReturnType
     */
    public function reduce(callable $callback, mixed $initial = null): mixed
    {
        $result = $initial;

        foreach ($this->items as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Create a collection of all elements that do not pass a given truth test.
     *
     * @param (callable(TValue, TKey): bool)|TValue $callback
     * @return static<TKey, TValue>
     */
    public function reject(mixed $callback = true): static
    {
        $useAsCallable = $this->useAsCallable($callback);

        return $this->filter(function ($value, $key) use ($callback, $useAsCallable) {
            return $useAsCallable
                ? !$callback($value, $key)
                : $value !== $callback;
        });
    }

    /**
     * Reverse items order.
     *
     * @return static<TKey, TValue>
     */
    public function reverse(): static
    {
        return new static(array_reverse($this->items, true));
    }

    /**
     * Search the collection for a given value and return the corresponding key if successful.
     *
     * @param TValue|(callable(TValue, TKey): bool) $value
     * @param bool $strict
     * @return TKey|false
     */
    public function search(mixed $value, bool $strict = false): mixed
    {
        if (!$this->useAsCallable($value)) {
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
     * @return TValue|null
     */
    public function shift(): mixed
    {
        return array_shift($this->items);
    }

    /**
     * Shuffle the items in the collection.
     *
     * @param int|null $seed
     * @return static<array-key, TValue>
     */
    public function shuffle(?int $seed = null): static
    {
        $items = $this->items;

        if ($seed !== null) {
            mt_srand($seed);
        }

        $keys = array_keys($items);
        shuffle($keys);

        $shuffled = [];
        foreach ($keys as $key) {
            $shuffled[] = $items[$key];
        }

        return new static($shuffled);
    }

    /**
     * Skip the first {$count} items.
     *
     * @param int $count
     * @return static<TKey, TValue>
     */
    public function skip(int $count): static
    {
        return $this->slice($count);
    }

    /**
     * Slice the underlying collection array.
     *
     * @param int $offset
     * @param int|null $length
     * @return static<TKey, TValue>
     */
    public function slice(int $offset, ?int $length = null): static
    {
        return new static(array_slice($this->items, $offset, $length, true));
    }

    /**
     * Split a collection into a certain number of groups.
     *
     * @param int $numberOfGroups
     * @return static<int, static<TKey, TValue>>
     */
    public function split(int $numberOfGroups): static
    {
        if ($this->isEmpty()) {
            return new static();
        }

        $groups = new static();

        $groupSize = (int) floor($this->count() / $numberOfGroups);
        $remain = $this->count() % $numberOfGroups;

        $start = 0;

        for ($i = 0; $i < $numberOfGroups; $i++) {
            $size = $groupSize;
            if ($i < $remain) {
                $size++;
            }

            if ($size) {
                $groups->push(new static(array_slice($this->items, $start, $size, true)));

                $start += $size;
            }
        }

        return $groups;
    }

    /**
     * Sort through each item with a callback.
     *
     * @param (callable(TValue, TValue): int)|int|null $callback
     * @return static<TKey, TValue>
     */
    public function sort(callable|int|null $callback = null): static
    {
        $items = $this->items;

        $callback && is_callable($callback)
            ? uasort($items, $callback)
            : asort($items, $callback ?? SORT_REGULAR);

        return new static($items);
    }

    /**
     * Sort the collection using the given callback.
     *
     * @param (callable(TValue, TKey): mixed)|string $callback
     * @param int $options
     * @param bool $descending
     * @return static<TKey, TValue>
     */
    public function sortBy(callable|string $callback, int $options = SORT_REGULAR, bool $descending = false): static
    {
        $results = [];

        $callback = $this->valueRetriever($callback);

        // First we will loop through the items and get the comparator from a callback
        // function which we were given. Then, we will sort the returned values and
        // grab all the corresponding values for the sorted keys from this array.
        foreach ($this->items as $key => $value) {
            $results[$key] = $callback($value, $key);
        }

        $descending ? arsort($results, $options) : asort($results, $options);

        // Once we have sorted all of the keys in the array, we will loop through them
        // and grab the corresponding model so we can set the underlying items list
        // to the sorted version. Then we'll just return the collection instance.
        foreach (array_keys($results) as $key) {
            $results[$key] = $this->items[$key];
        }

        return new static($results);
    }

    /**
     * Sort the collection in descending order using the given callback.
     *
     * @param (callable(TValue, TKey): mixed)|string $callback
     * @param int $options
     * @return static<TKey, TValue>
     */
    public function sortByDesc(callable|string $callback, int $options = SORT_REGULAR): static
    {
        return $this->sortBy($callback, $options, true);
    }

    /**
     * Sort the collection keys.
     *
     * @param int $options
     * @param bool $descending
     * @return static<TKey, TValue>
     */
    public function sortKeys(int $options = SORT_REGULAR, bool $descending = false): static
    {
        $items = $this->items;

        $descending ? krsort($items, $options) : ksort($items, $options);

        return new static($items);
    }

    /**
     * Sort the collection keys in descending order.
     *
     * @param int $options
     * @return static<TKey, TValue>
     */
    public function sortKeysDesc(int $options = SORT_REGULAR): static
    {
        return $this->sortKeys($options, true);
    }

    /**
     * Splice a portion of the underlying collection array.
     *
     * @param int $offset
     * @param int|null $length
     * @param iterable<array-key, TValue> $replacement
     * @return static<TKey, TValue>
     */
    public function splice(int $offset, ?int $length = null, iterable $replacement = []): static
    {
        if (func_num_args() === 1) {
            return new static(array_splice($this->items, $offset));
        }

        return new static(array_splice($this->items, $offset, $length, $this->getArrayableItems($replacement)));
    }

    /**
     * Get the sum of the given values.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $callback
     * @return mixed
     */
    public function sum(?callable $callback = null): mixed
    {
        $callback = is_null($callback) ? $this->identity() : $this->valueRetriever($callback);

        return $this->reduce(function ($result, $item) use ($callback) {
            return $result + $callback($item);
        }, 0);
    }

    /**
     * Take the first or last {$limit} items.
     *
     * @param int $limit
     * @return static<TKey, TValue>
     */
    public function take(int $limit): static
    {
        if ($limit < 0) {
            return $this->slice($limit, abs($limit));
        }

        return $this->slice(0, $limit);
    }

    /**
     * Transform each item in the collection using a callback.
     *
     * @param callable(TValue, TKey): TValue $callback
     * @return $this
     */
    public function transform(callable $callback): static
    {
        $this->items = $this->map($callback)->all();

        return $this;
    }

    /**
     * Return only unique items from the collection array.
     *
     * @param (callable(TValue, TKey): mixed)|string|null $key
     * @param bool $strict
     * @return static<TKey, TValue>
     */
    public function unique(callable|string|null $key = null, bool $strict = false): static
    {
        $callback = $this->valueRetriever($key);

        $exists = [];

        return $this->reject(function ($item, $key) use ($callback, $strict, &$exists) {
            if (in_array($id = $callback($item, $key), $exists, $strict)) {
                return true;
            }

            $exists[] = $id;
        });
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return static<int, TValue>
     */
    public function values(): static
    {
        return new static(array_values($this->items));
    }

    /**
     * Filter items by the given key value pair.
     *
     * @param callable|string $key
     * @param mixed $operator
     * @param mixed $value
     * @return static<TKey, TValue>
     */
    public function where(callable|string $key, mixed $operator = null, mixed $value = null): static
    {
        return $this->filter($this->operatorForWhere(...func_get_args()));
    }

    /**
     * Filter items where the given key is between the given values.
     *
     * @param string $key
     * @param iterable<mixed> $values
     * @return static<TKey, TValue>
     */
    public function whereBetween(string $key, iterable $values): static
    {
        return $this->where($key, '>=', reset($values))->where($key, '<=', end($values));
    }

    /**
     * Filter items where the given key is in the given array.
     *
     * @param string $key
     * @param iterable<mixed> $values
     * @param bool $strict
     * @return static<TKey, TValue>
     */
    public function whereIn(string $key, iterable $values, bool $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->filter(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }

    /**
     * Filter items where the given key is not between the given values.
     *
     * @param string $key
     * @param iterable<mixed> $values
     * @return static<TKey, TValue>
     */
    public function whereNotBetween(string $key, iterable $values): static
    {
        return $this->filter(function ($item) use ($key, $values) {
            return data_get($item, $key) < reset($values) || data_get($item, $key) > end($values);
        });
    }

    /**
     * Filter items where the given key is not in the given array.
     *
     * @param string $key
     * @param iterable<mixed> $values
     * @param bool $strict
     * @return static<TKey, TValue>
     */
    public function whereNotIn(string $key, iterable $values, bool $strict = false): static
    {
        $values = $this->getArrayableItems($values);

        return $this->reject(function ($item) use ($key, $values, $strict) {
            return in_array(data_get($item, $key), $values, $strict);
        });
    }

    /**
     * Filter items where the given key is not null.
     *
     * @param string|null $key
     * @return static<TKey, TValue>
     */
    public function whereNotNull(?string $key = null): static
    {
        return $this->whereNotIn($key, [null], true);
    }

    /**
     * Filter items where the given key is null.
     *
     * @param string|null $key
     * @return static<TKey, TValue>
     */
    public function whereNull(?string $key = null): static
    {
        return $this->whereIn($key, [null], true);
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param iterable<mixed> ...$items
     * @return static<int, static<int, mixed>>
     */
    public function zip(...$items): static
    {
        $arrayableItems = array_map([$this, 'getArrayableItems'], func_get_args());

        $params = array_merge([function () {
            return new static(func_get_args());
        }, $this->items], $arrayableItems);

        return new static(array_map(...$params));
    }

    /**
     * Pad collection to the specified length with a value.
     *
     * @param int $size
     * @param TValue $value
     * @return static<int, TValue>
     */
    public function pad(int $size, mixed $value): static
    {
        return new static(array_pad($this->items, $size, $value));
    }

    /**
     * Get the collection of items as a plain array.
     *
     * @return array<TKey, mixed>
     */
    public function toArray(): array
    {
        return array_map(function ($value) {
            return $value instanceof self ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get the collection of items as JSON.
     *
     * @return array<TKey, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof self) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return ArrayIterator<TKey, TValue>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param TKey $key
     * @return bool
     */
    public function offsetExists(mixed $key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param TKey $key
     * @return TValue
     */
    public function offsetGet(mixed $key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param TKey|null $key
     * @param TValue $value
     * @return void
     */
    public function offsetSet(mixed $key, mixed $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param TKey $key
     * @return void
     */
    public function offsetUnset(mixed $key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * Get a value retrieving callback.
     *
     * @param callable|string|null $value
     * @return callable
     */
    protected function valueRetriever(mixed $value): callable
    {
        if ($this->useAsCallable($value)) {
            return $value;
        }

        return function ($item) use ($value) {
            return data_get($item, $value);
        };
    }

    /**
     * Determine if the given value is callable, but not a string.
     *
     * @param mixed $value
     * @return bool
     */
    protected function useAsCallable(mixed $value): bool
    {
        return !is_string($value) && is_callable($value);
    }

    /**
     * Get an operator checker callback.
     *
     * @param callable|string $key
     * @param string|null $operator
     * @param mixed $value
     * @return callable
     */
    protected function operatorForWhere(mixed $key, mixed $operator = null, mixed $value = null): callable
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

            if (count($strings) < 2 && count(array_filter([$retrieved, $value], 'is_object')) === 1) {
                return in_array($operator, ['!=', '<>', '!==']);
            }

            switch ($operator) {
                default:
                case '=':
                case '==':
                    return $retrieved == $value;
                case '!=':
                case '<>':
                    return $retrieved != $value;
                case '<':
                    return $retrieved < $value;
                case '>':
                    return $retrieved > $value;
                case '<=':
                    return $retrieved <= $value;
                case '>=':
                    return $retrieved >= $value;
                case '===':
                    return $retrieved === $value;
                case '!==':
                    return $retrieved !== $value;
            }
        };
    }

    /**
     * Results array of items from Collection or Arrayable.
     *
     * @param iterable<array-key, mixed> $items
     * @return array<array-key, mixed>
     */
    protected function getArrayableItems(iterable $items): array
    {
        if (is_array($items)) {
            return $items;
        }

        return $items instanceof self ? $items->all() : iterator_to_array($items);
    }

    /**
     * Get an identity function that returns its argument.
     *
     * @return callable
     */
    protected function identity(): callable
    {
        return function ($value) {
            return $value;
        };
    }
}