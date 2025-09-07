<?php

declare(strict_types=1);

namespace Lythany\Support\Facades;

/**
 * Abstract Facade Class
 * 
 * Base class for all facade implementations in Lythany.
 * Provides static access to registered services through the service container.
 * 
 * @package Lythany\Support\Facades
 */
abstract class Facade
{
    /**
     * The application instance being facaded.
     */
    protected static ?object $app = null;
    
    /**
     * The resolved object instances.
     */
    protected static array $resolvedInstance = [];
    
    /**
     * Get the registered name of the component.
     *
     * @return string
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor(): string
    {
        throw new \RuntimeException('Facade does not define a facade accessor.');
    }
    
    /**
     * Get the application instance behind the facade.
     *
     * @return object
     */
    public static function getFacadeApplication(): object
    {
        return static::$app;
    }
    
    /**
     * Set the application instance.
     *
     * @param object $app Application instance
     */
    public static function setFacadeApplication(object $app): void
    {
        static::$app = $app;
    }
    
    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     */
    public static function clearResolvedInstance(string $name): void
    {
        unset(static::$resolvedInstance[$name]);
    }
    
    /**
     * Clear all resolved instances.
     */
    public static function clearResolvedInstances(): void
    {
        static::$resolvedInstance = [];
    }
    
    /**
     * Get the application instance from the facade.
     *
     * @return object
     */
    public static function getFacadeRoot(): object
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }
    
    /**
     * Resolve the facade root instance from the container.
     *
     * @param string $name
     * @return mixed
     */
    protected static function resolveFacadeInstance(string $name)
    {
        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }
        
        if (static::$app) {
            // If we have a container, resolve from it
            if (method_exists(static::$app, 'make')) {
                return static::$resolvedInstance[$name] = static::$app->make($name);
            }
            
            // If we have a simple registry, get from it
            if (method_exists(static::$app, 'get')) {
                return static::$resolvedInstance[$name] = static::$app->get($name);
            }
        }
        
        // If no container, throw exception
        throw new \RuntimeException("A facade root has not been set for [{$name}].");
    }
    
    /**
     * Handle dynamic, static calls to the object.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws \RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getFacadeRoot();
        
        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }
        
        return $instance->$method(...$args);
    }
}
