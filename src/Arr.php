<?php

declare(strict_types=1);

namespace Lythany\Support;

/**
 * Array Helper Class
 * 
 * Provides array manipulation and utility methods
 * 
 * @package Lythany\Support
 */
class Arr
{
    /**
     * Filter the array using the given callback
     * 
     * @param array $array
     * @param callable $callback
     * @return array
     */
    public static function where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get an item from an array using "dot" notation
     * 
     * @param array $array
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $array, string|int|null $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        // Convert key to string if it's an integer for dot notation processing
        $keyString = (string) $key;

        foreach (explode('.', $keyString) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation
     * 
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public static function set(array &$array, string $key, mixed $value): array
    {
        $keys = explode('.', $key);

        while (count($keys) > 1) {
            $key = array_shift($keys);

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Flatten a multi-dimensional array into a single level
     * 
     * @param array $array
     * @param int $depth
     * @return array
     */
    public static function flatten(array $array, int $depth = PHP_INT_MAX): array
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item) && $depth > 0) {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    /**
     * Pluck an array of values from an array
     * 
     * @param array $array
     * @param string $value
     * @param string|null $key
     * @return array
     */
    public static function pluck(array $array, string $value, ?string $key = null): array
    {
        $results = [];

        foreach ($array as $item) {
            $itemValue = static::get((array) $item, $value);

            if ($key === null) {
                $results[] = $itemValue;
            } else {
                $itemKey = static::get((array) $item, $key);
                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }

    /**
     * Push an item onto the beginning of an array
     * 
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     */
    public static function prepend(array $array, mixed $value, mixed $key = null): array
    {
        if ($key === null) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }

    /**
     * Get a subset of the items from the given array
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Get all of the given array except for a specified array of keys
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    public static function except(array $array, array|string $keys): array
    {
        static::forget($array, $keys);

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation
     * 
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    public static function forget(array &$array, array|string $keys): void
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                unset($array[$key]);
                continue;
            }

            $parts = explode('.', $key);
            $array = &$original;

            while (count($parts) > 1) {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part])) {
                    $array = &$array[$part];
                } else {
                    continue 2;
                }
            }

            unset($array[array_shift($parts)]);
        }
    }

    /**
     * Determine if the given key exists in the provided array
     * 
     * @param array $array
     * @param string $key
     * @return bool
     */
    public static function has(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if any of the keys exist in an array using "dot" notation
     * 
     * @param array $array
     * @param string|array $keys
     * @return bool
     */
    public static function hasAny(array $array, string|array $keys): bool
    {
        $keys = (array) $keys;

        if (empty($array) || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (static::has($array, $key)) {
                return true;
            }
        }

        return false;
    }
}
