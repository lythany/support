<?php

declare(strict_types=1);

namespace Lythany\Support\Traits;

use BadMethodCallException;
use Closure;
use ReflectionClass;
use ReflectionMethod;

/**
 * Macroable trait allows classes to be extended at runtime
 *
 * This trait provides the ability to add custom methods to classes dynamically,
 * enabling powerful extension patterns and flexible APIs.
 *
 * @package Lythany\Support\Traits
 * @author Lythany Framework Team
 * @since 1.0.0
 */
trait Macroable
{
    /**
     * The registered string macros
     *
     * @var array<string, array<string, callable>>
     */
    protected static array $macros = [];

    /**
     * Register a custom macro
     *
     * @param string $name
     * @param object|callable $macro
     * @return void
     */
    public static function macro(string $name, object|callable $macro): void
    {
        static::$macros[static::class][$name] = $macro;
    }

    /**
     * Mix another object into the class
     *
     * @param object $mixin
     * @param bool $replace
     * @return void
     * @throws \ReflectionException
     */
    public static function mixin(object $mixin, bool $replace = true): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || !static::hasMacro($method->name)) {
                $method->setAccessible(true);
                $methodResult = $method->invoke($mixin);
                
                // Only register if the result is callable (Closure)
                if (is_callable($methodResult)) {
                    static::macro($method->name, $methodResult);
                }
            }
        }
    }

    /**
     * Flush the existing macros
     *
     * @return void
     */
    public static function flushMacros(): void
    {
        static::$macros[static::class] = [];
    }

    /**
     * Checks if macro is registered
     *
     * @param string $name
     * @return bool
     */
    public static function hasMacro(string $name): bool
    {
        // Check current class first
        if (isset(static::$macros[static::class][$name])) {
            return true;
        }
        
        // Check parent classes for inheritance
        $parentClass = get_parent_class(static::class);
        while ($parentClass) {
            if (isset(static::$macros[$parentClass][$name])) {
                return true;
            }
            $parentClass = get_parent_class($parentClass);
        }
        
        return false;
    }

    /**
     * Get a macro from the current class or inherited from parent classes
     *
     * @param string $name
     * @return callable|null
     */
    protected static function getMacro(string $name): ?callable
    {
        // Check current class first
        if (isset(static::$macros[static::class][$name])) {
            return static::$macros[static::class][$name];
        }
        
        // Check parent classes for inheritance
        $parentClass = get_parent_class(static::class);
        while ($parentClass) {
            if (isset(static::$macros[$parentClass][$name])) {
                return static::$macros[$parentClass][$name];
            }
            $parentClass = get_parent_class($parentClass);
        }
        
        return null;
    }

    /**
     * Get all registered macros
     *
     * @return array<string, callable>
     */
    public static function getMacros(): array
    {
        return static::$macros[static::class] ?? [];
    }

    /**
     * Dynamically handle calls to the class
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function __callStatic(string $method, array $parameters): mixed
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                "Method '%s' does not exist",
                $method
            ));
        }

        $macro = static::getMacro($method);

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo(null, static::class);
        }

        return $macro(...$parameters);
    }

    /**
     * Dynamically handle calls to the class
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): mixed
    {
        if (!static::hasMacro($method)) {
            throw new BadMethodCallException(sprintf(
                "Method '%s' does not exist",
                $method
            ));
        }

        $macro = static::getMacro($method);

        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($this, static::class);
        }

        return $macro(...$parameters);
    }
}
