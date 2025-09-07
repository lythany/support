<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

use Lythany\Support\Facade;

/**
 * Array Helper Facade
 * 
 * @method static array where(array $array, callable $callback)
 * @method static mixed get(array $array, string|int|null $key = null, mixed $default = null)
 * @method static array set(array &$array, string $key, mixed $value)
 * @method static array flatten(array $array, int $depth = PHP_INT_MAX)
 * @method static array pluck(array $array, string $value, string|null $key = null)
 * @method static array prepend(array $array, mixed $value, mixed $key = null)
 * @method static array only(array $array, array|string $keys)
 * @method static array except(array $array, array|string $keys)
 * @method static void forget(array &$array, array|string $keys)
 * @method static bool has(array $array, string $key)
 * @method static bool hasAny(array $array, string|array $keys)
 * 
 * @package Lythany\Support\Facades
 */
class ArrFacade extends Facade
{
    /**
     * Get the registered name of the component
     * 
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'arr';
    }
}