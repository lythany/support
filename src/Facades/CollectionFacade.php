<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

use Lythany\Support\Facade;

/**
 * Collection Helper Facade
 * 
 * @method static \Lythany\Support\Collection make(iterable $items = [])
 * @method static \Lythany\Support\Collection wrap(mixed $value)
 * @method static \Lythany\Support\Collection times(int $number, callable|null $callback = null)
 * @method static \Lythany\Support\Collection range(int $from, int $to)
 * 
 * @package Lythany\Support\Facades
 */
class CollectionFacade extends Facade
{
    /**
     * Get the registered name of the component
     * 
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'collection';
    }
}