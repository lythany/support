<?php

declare(strict_types=1);

namespace Lythany\Support\Macros;

use BadMethodCallException;
use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionException;

/**
 * Macro Manager for handling class extensions
 *
 * Provides centralized management of macros across the framework,
 * including conditional macros, macro namespacing, and advanced extension patterns.
 *
 * @package Lythany\Support\Macros
 * @author Lythany Framework Team
 * @since 1.0.0
 */
class MacroManager
{
    /**
     * Global macros registry
     *
     * @var array<string, array<string, callable>>
     */
    protected static array $globalMacros = [];

    /**
     * Conditional macros registry
     *
     * @var array<string, array<string, array{macro: callable, condition: callable}>>
     */
    protected static array $conditionalMacros = [];

    /**
     * Macro namespaces
     *
     * @var array<string, array<string, callable>>
     */
    protected static array $namespacedMacros = [];

    /**
     * Disabled macro names
     *
     * @var array<string, array<string, bool>>
     */
    protected static array $disabledMacros = [];

    /**
     * Register a global macro for a class
     *
     * @param string $className
     * @param string $name
     * @param callable $macro
     * @return void
     */
    public static function register(string $className, string $name, callable $macro): void
    {
        if (empty($className)) {
            throw new InvalidArgumentException('Class name cannot be empty');
        }
        
        if (empty($name)) {
            throw new InvalidArgumentException('Macro name cannot be empty');
        }
        
        static::$globalMacros[$className][$name] = $macro;
    }

    /**
     * Register a conditional macro
     *
     * @param string $className
     * @param string $name
     * @param callable $macro
     * @param callable $condition
     * @return void
     */
    public static function registerConditional(string $className, string $name, callable $macro, callable $condition): void
    {
        static::$conditionalMacros[$className][$name] = [
            'macro' => $macro,
            'condition' => $condition,
        ];
    }

    /**
     * Register a namespaced macro
     *
     * @param string $namespace
     * @param string $className
     * @param string $name
     * @param callable $macro
     * @return void
     */
    public static function registerNamespaced(string $namespace, string $className, string $name, callable $macro): void
    {
        if (empty($namespace)) {
            throw new InvalidArgumentException('Namespace cannot be empty');
        }
        
        if (empty($className)) {
            throw new InvalidArgumentException('Class name cannot be empty');
        }
        
        if (empty($name)) {
            throw new InvalidArgumentException('Macro name cannot be empty');
        }
        
        static::$namespacedMacros[$namespace][$className][$name] = $macro;
    }

    /**
     * Mix an object into a class globally
     *
     * @param string $className
     * @param object $mixin
     * @param bool $replace
     * @return void
     * @throws ReflectionException
     */
    public static function mixin(string $className, object $mixin, bool $replace = true): void
    {
        $methods = (new ReflectionClass($mixin))->getMethods(
            ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED
        );

        foreach ($methods as $method) {
            if ($replace || !static::hasGlobalMacro($className, $method->name)) {
                $method->setAccessible(true);
                static::register($className, $method->name, $method->invoke($mixin));
            }
        }
    }

    /**
     * Check if a global macro exists
     *
     * @param string $className
     * @param string $name
     * @return bool
     */
    public static function hasGlobalMacro(string $className, string $name): bool
    {
        return isset(static::$globalMacros[$className][$name]);
    }

    /**
     * Check if a conditional macro exists and its condition is met
     *
     * @param string $className
     * @param string $name
     * @param array $parameters
     * @return bool
     */
    public static function hasConditionalMacro(string $className, string $name, array $parameters = []): bool
    {
        if (!isset(static::$conditionalMacros[$className][$name]) || static::isMacroDisabled($className, $name)) {
            return false;
        }

        $condition = static::$conditionalMacros[$className][$name]['condition'];
        
        return $condition($parameters);
    }

    /**
     * Check if a namespaced macro exists
     *
     * @param string $namespace
     * @param string $className
     * @param string $name
     * @return bool
     */
    public static function hasNamespacedMacro(string $namespace, string $className, string $name): bool
    {
        return isset(static::$namespacedMacros[$namespace][$className][$name]) && 
               !static::isMacroDisabled($className, $name);
    }

    /**
     * Get a global macro
     *
     * @param string $className
     * @param string $name
     * @return callable|null
     */
    public static function getGlobalMacro(string $className, string $name): ?callable
    {
        return static::$globalMacros[$className][$name] ?? null;
    }

    /**
     * Get a conditional macro
     *
     * @param string $className
     * @param string $name
     * @return callable|null
     */
    public static function getConditionalMacro(string $className, string $name): ?callable
    {
        return static::$conditionalMacros[$className][$name]['macro'] ?? null;
    }

    /**
     * Get a namespaced macro
     *
     * @param string $namespace
     * @param string $className
     * @param string $name
     * @return callable|null
     */
    public static function getNamespacedMacro(string $namespace, string $className, string $name): ?callable
    {
        return static::$namespacedMacros[$namespace][$className][$name] ?? null;
    }

    /**
     * Execute a macro
     *
     * @param string $className
     * @param string $name
     * @param array $parameters
     * @param object|null $instance
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function call(string $className, string $name, array $parameters = [], ?object $instance = null): mixed
    {
        // Try global macros first
        if (static::hasGlobalMacro($className, $name)) {
            $macro = static::getGlobalMacro($className, $name);
            return static::executeMacro($macro, $parameters, $instance, $className);
        }

        // Try conditional macros
        if (static::hasConditionalMacro($className, $name, $parameters)) {
            $macro = static::getConditionalMacro($className, $name);
            return static::executeMacro($macro, $parameters, $instance, $className);
        }

        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            $className,
            $name
        ));
    }

    /**
     * Execute a namespaced macro
     *
     * @param string $namespace
     * @param string $className
     * @param string $name
     * @param array $parameters
     * @param object|null $instance
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function callNamespaced(string $namespace, string $className, string $name, array $parameters = [], ?object $instance = null): mixed
    {
        if (!static::hasNamespacedMacro($namespace, $className, $name)) {
            throw new BadMethodCallException(sprintf(
                'Namespaced method %s::%s::%s does not exist.',
                $namespace,
                $className,
                $name
            ));
        }

        $macro = static::getNamespacedMacro($namespace, $className, $name);
        return static::executeMacro($macro, $parameters, $instance, $className);
    }

    /**
     * Execute a macro with proper binding
     *
     * @param callable $macro
     * @param array $parameters
     * @param object|null $instance
     * @param string $className
     * @return mixed
     */
    protected static function executeMacro(callable $macro, array $parameters, ?object $instance, string $className): mixed
    {
        if ($macro instanceof Closure) {
            $macro = $macro->bindTo($instance, $instance ? get_class($instance) : $className);
        }

        return $macro(...$parameters);
    }

    /**
     * Disable a macro
     *
     * @param string $className
     * @param string $name
     * @return void
     */
    public static function disable(string $className, string $name): void
    {
        static::$disabledMacros[$className][$name] = true;
    }

    /**
     * Enable a macro
     *
     * @param string $className
     * @param string $name
     * @return void
     */
    public static function enable(string $className, string $name): void
    {
        unset(static::$disabledMacros[$className][$name]);
    }

    /**
     * Check if a macro is disabled
     *
     * @param string $className
     * @param string $name
     * @return bool
     */
    public static function isMacroDisabled(string $className, string $name): bool
    {
        return static::$disabledMacros[$className][$name] ?? false;
    }

    /**
     * Get all macros for a class
     *
     * @param string $className
     * @return array<string, array<string, callable>>
     */
    public static function getAllMacros(string $className): array
    {
        $result = [
            'global' => [],
            'conditional' => [],
        ];

        // Global macros
        if (isset(static::$globalMacros[$className])) {
            foreach (static::$globalMacros[$className] as $name => $macro) {
                if (!static::isMacroDisabled($className, $name)) {
                    $result['global'][$name] = $macro;
                }
            }
        }

        // Conditional macros
        if (isset(static::$conditionalMacros[$className])) {
            foreach (static::$conditionalMacros[$className] as $name => $macroData) {
                if (!static::isMacroDisabled($className, $name)) {
                    $result['conditional'][$name] = $macroData['macro'];
                }
            }
        }

        return $result;
    }

    /**
     * Get all namespaced macros for a class
     *
     * @param string $namespace
     * @param string $className
     * @return array<string, callable>
     */
    public static function getAllNamespacedMacros(string $namespace, string $className): array
    {
        $macros = [];

        if (isset(static::$namespacedMacros[$namespace][$className])) {
            foreach (static::$namespacedMacros[$namespace][$className] as $name => $macro) {
                if (!static::isMacroDisabled($className, $name)) {
                    $macros[$name] = $macro;
                }
            }
        }

        return $macros;
    }

    /**
     * Remove a macro
     *
     * @param string $className
     * @param string $name
     * @return void
     */
    public static function remove(string $className, string $name): void
    {
        unset(static::$globalMacros[$className][$name]);
        unset(static::$conditionalMacros[$className][$name]);
        unset(static::$disabledMacros[$className][$name]);
    }

    /**
     * Remove a namespaced macro
     *
     * @param string $namespace
     * @param string $className
     * @param string $name
     * @return void
     */
    public static function removeNamespaced(string $namespace, string $className, string $name): void
    {
        unset(static::$namespacedMacros[$namespace][$className][$name]);
    }

    /**
     * Flush all macros for a class
     *
     * @param string $className
     * @return void
     */
    /**
     * Flush all macros or macros for a specific class
     *
     * @param string|null $className The class name, or null to flush all
     * @return void
     */
    public static function flush(?string $className = null): void
    {
        if ($className === null) {
            // Flush all macros
            static::$globalMacros = [];
            static::$conditionalMacros = [];
            static::$namespacedMacros = [];
            static::$disabledMacros = [];
        } else {
            // Flush macros for specific class
            unset(static::$globalMacros[$className]);
            unset(static::$conditionalMacros[$className]);
            unset(static::$disabledMacros[$className]);
        }
    }

    /**
     * Flush all macros in a namespace
     *
     * @param string $namespace
     * @return void
     */
    public static function flushNamespace(string $namespace): void
    {
        unset(static::$namespacedMacros[$namespace]);
    }

    /**
     * Flush all macros
     *
     * @return void
     */
    public static function flushAll(): void
    {
        static::$globalMacros = [];
        static::$conditionalMacros = [];
        static::$namespacedMacros = [];
        static::$disabledMacros = [];
    }

    /**
     * Get macro statistics
     *
     * @return array<string, int>
     */
    public static function getStatistics(): array
    {
        $globalCount = 0;
        $conditionalCount = 0;
        $namespacedCount = 0;
        $disabledCount = 0;

        foreach (static::$globalMacros as $classMacros) {
            $globalCount += count($classMacros);
        }

        foreach (static::$conditionalMacros as $classMacros) {
            $conditionalCount += count($classMacros);
        }

        foreach (static::$namespacedMacros as $namespaceMacros) {
            foreach ($namespaceMacros as $classMacros) {
                if (is_array($classMacros)) {
                    $namespacedCount += count($classMacros);
                }
            }
        }

        foreach (static::$disabledMacros as $classMacros) {
            $disabledCount += count($classMacros);
        }

        return [
            'global' => $globalCount,
            'conditional' => $conditionalCount,
            'namespaced' => $namespacedCount,
            'disabled' => $disabledCount,
            'total' => $globalCount + $conditionalCount + $namespacedCount,
        ];
    }

    /**
     * Resolve a macro for a class
     *
     * @param string $className The class name
     * @param string $name The macro name
     * @return callable|null The macro implementation or null if not found
     */
    public static function resolve(string $className, string $name): ?callable
    {
        // Check if macro is disabled
        if (static::isMacroDisabled($className, $name)) {
            return null;
        }

        // Try to get global macro first
        return static::getGlobalMacro($className, $name);
    }

    /**
     * Resolve a conditional macro for a class
     *
     * @param string $className The class name
     * @param string $name The macro name
     * @param array $parameters The parameters to check condition against
     * @return callable|null The macro implementation or null if not found or condition not met
     */
    public static function resolveConditional(string $className, string $name, array $parameters = []): ?callable
    {
        // Check if macro is disabled
        if (static::isMacroDisabled($className, $name)) {
            return null;
        }

        // Check if conditional macro exists and condition is met
        if (!static::hasConditionalMacro($className, $name, $parameters)) {
            return null;
        }

        return static::getConditionalMacro($className, $name);
    }

    /**
     * Resolve a namespaced macro for a class
     *
     * @param string $namespace The namespace
     * @param string $className The class name
     * @param string $name The macro name
     * @return callable|null The macro implementation or null if not found
     */
    public static function resolveNamespaced(string $namespace, string $className, string $name): ?callable
    {
        return static::getNamespacedMacro($namespace, $className, $name);
    }

    /**
     * Remove a conditional macro
     *
     * @param string $className The class name
     * @param string $name The macro name
     * @return void
     */
    public static function removeConditional(string $className, string $name): void
    {
        if (isset(static::$conditionalMacros[$className][$name])) {
            unset(static::$conditionalMacros[$className][$name]);

            if (empty(static::$conditionalMacros[$className])) {
                unset(static::$conditionalMacros[$className]);
            }
        }
    }

    /**
     * Flush all macros for a specific class
     *
     * @param string $className The class name
     * @return void
     */
    public static function flushForClass(string $className): void
    {
        static::flush($className);
    }

    /**
     * Create a macro builder for fluent API
     *
     * @param string $className
     * @param string $name
     * @return MacroBuilder
     */
    public static function builder(string $className, string $name): MacroBuilder
    {
        return new MacroBuilder($className, $name);
    }
}
