<?php

declare(strict_types=1);

namespace Lythany\Support;

use RuntimeException;

/**
 * Facade Base Class
 * 
 * Provides static proxy functionality for accessing services.
 */
abstract class Facade
{
    /**
     * The application instance being facaded.
     */
    protected static $app;

    /**
     * The resolved object instances.
     */
    protected static array $resolvedInstance = [];

    /**
     * Indicates if the resolved instances should be cached.
     */
    protected static bool $cached = true;

    /**
     * Get the registered name of the component.
     */
    abstract protected static function getFacadeAccessor(): string;

    /**
     * Get the root object behind the facade.
     */
    public static function getFacadeRoot(): mixed
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Resolve the facade root instance from the container.
     */
    protected static function resolveFacadeInstance(string $name): mixed
    {
        if (isset(static::$resolvedInstance[$name]) && static::$cached) {
            return static::$resolvedInstance[$name];
        }

        if (static::$app) {
            if (method_exists(static::$app, 'make')) {
                return static::$resolvedInstance[$name] = static::$app->make($name);
            }
        }

        throw new RuntimeException("A facade root has not been set for [{$name}].");
    }

    /**
     * Clear a resolved facade instance.
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }

    /**
     * Get the application instance behind the facade.
     */
    public static function getFacadeApplication(): mixed
    {
        return static::$app;
    }

    /**
     * Set the application instance.
     */
    public static function setFacadeApplication($app): void
    {
        static::$app = $app;
    }

    /**
     * Handle dynamic, static calls to the object.
     */
    public static function __callStatic(string $method, array $args): mixed
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        return $instance->$method(...$args);
    }
}
