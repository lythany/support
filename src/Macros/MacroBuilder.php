<?php

declare(strict_types=1);

namespace Lythany\Support\Macros;

use Closure;
use InvalidArgumentException;

/**
 * MacroBuilder - Fluent API for building macros
 *
 * Provides a fluent interface for creating complex macro configurations
 * with conditional logic, validation, and metadata.
 */
class MacroBuilder
{
    private string $className;
    private string $name;
    private ?Closure $implementation = null;
    private ?Closure $condition = null;
    private array $metadata = [];
    private bool $overwrite = false;
    private ?string $namespace = null;
    private array $validationRules = [];
    private ?string $description = null;
    private array $tags = [];
    private int $priority = 0;

    /**
     * Create a new macro builder
     *
     * @param string $className The class name to register the macro for
     * @param string $name The macro name
     */
    public function __construct(string $className, string $name)
    {
        $this->className = $className;
        $this->name = $name;
    }

    /**
     * Set the macro implementation
     *
     * @param callable $callback The macro implementation
     * @return $this
     */
    public function implement(callable $callback): self
    {
        $this->implementation = Closure::fromCallable($callback);
        return $this;
    }

    /**
     * Set a condition for when the macro should be available
     *
     * @param callable $condition The condition callback
     * @return $this
     */
    public function when(callable $condition): self
    {
        $this->condition = Closure::fromCallable($condition);
        return $this;
    }

    /**
     * Set macro metadata
     *
     * @param array $metadata The metadata array
     * @return $this
     */
    public function withMetadata(array $metadata): self
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Add a single metadata entry
     *
     * @param string $key The metadata key
     * @param mixed $value The metadata value
     * @return $this
     */
    public function addMetadata(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Allow overwriting existing macros
     *
     * @param bool $overwrite Whether to allow overwriting
     * @return $this
     */
    public function overwrite(bool $overwrite = true): self
    {
        $this->overwrite = $overwrite;
        return $this;
    }

    /**
     * Set the namespace for the macro
     *
     * @param string $namespace The namespace
     * @return $this
     */
    public function inNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * Add validation rules for macro parameters
     *
     * @param array $rules The validation rules
     * @return $this
     */
    public function validateWith(array $rules): self
    {
        $this->validationRules = array_merge($this->validationRules, $rules);
        return $this;
    }

    /**
     * Add a single validation rule
     *
     * @param string $parameter The parameter name
     * @param callable $rule The validation rule
     * @return $this
     */
    public function validateParameter(string $parameter, callable $rule): self
    {
        $this->validationRules[$parameter] = Closure::fromCallable($rule);
        return $this;
    }

    /**
     * Set the macro description
     *
     * @param string $description The description
     * @return $this
     */
    public function describe(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Add tags to the macro
     *
     * @param string ...$tags The tags to add
     * @return $this
     */
    public function tag(string ...$tags): self
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }

    /**
     * Set the macro priority
     *
     * @param int $priority The priority (higher = executed first)
     * @return $this
     */
    public function priority(int $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Create a chainable macro that returns the instance
     *
     * @param callable $callback The macro implementation
     * @return $this
     */
    public function chainable(callable $callback): self
    {
        $originalCallback = Closure::fromCallable($callback);
        
        $this->implementation = function (...$args) use ($originalCallback) {
            $result = $originalCallback(...$args);
            
            // If the callback doesn't return anything or returns null,
            // return the instance for chaining
            if ($result === null) {
                return $this;
            }
            
            return $result;
        };

        return $this;
    }

    /**
     * Create a macro that caches its result
     *
     * @param callable $callback The macro implementation
     * @param int $ttl Cache time-to-live in seconds (0 = forever)
     * @return $this
     */
    public function cached(callable $callback, int $ttl = 300): self
    {
        $cache = [];
        $originalCallback = Closure::fromCallable($callback);
        
        $this->implementation = function (...$args) use ($originalCallback, &$cache, $ttl) {
            // Create a secure cache key from arguments without using serialize
            // This prevents potential security issues with malicious serialized data
            $key = hash('sha256', json_encode($args, JSON_THROW_ON_ERROR));
            $now = time();
            
            // Check if we have a cached result that's still valid
            if (isset($cache[$key]) && ($ttl === 0 || $cache[$key]['expires'] > $now)) {
                return $cache[$key]['value'];
            }
            
            // Execute the callback and cache the result
            $result = $originalCallback(...$args);
            $cache[$key] = [
                'value' => $result,
                'expires' => $ttl === 0 ? PHP_INT_MAX : $now + $ttl,
            ];
            
            return $result;
        };

        return $this;
    }

    /**
     * Create a macro that logs its execution
     *
     * @param callable $callback The macro implementation
     * @param string $logLevel The log level (debug, info, warning, error)
     * @return $this
     */
    public function logged(callable $callback, string $logLevel = 'debug'): self
    {
        $originalCallback = Closure::fromCallable($callback);
        
        $this->implementation = function (...$args) use ($originalCallback, $logLevel) {
            $startTime = microtime(true);
            
            // Log execution start
            $this->log($logLevel, "Executing macro '{$this->name}' on '{$this->className}'", [
                'args' => $args,
                'timestamp' => $startTime,
            ]);
            
            try {
                $result = $originalCallback(...$args);
                
                // Log successful execution
                $this->log($logLevel, "Macro '{$this->name}' executed successfully", [
                    'execution_time' => microtime(true) - $startTime,
                    'result_type' => gettype($result),
                ]);
                
                return $result;
            } catch (\Throwable $e) {
                // Log execution error
                $this->log('error', "Macro '{$this->name}' execution failed: {$e->getMessage()}", [
                    'exception' => $e,
                    'execution_time' => microtime(true) - $startTime,
                ]);
                
                throw $e;
            }
        };

        return $this;
    }

    /**
     * Register the macro
     *
     * @return void
     * @throws InvalidArgumentException If no implementation is provided
     */
    public function register(): void
    {
        if ($this->implementation === null) {
            throw new InvalidArgumentException("Macro '{$this->name}' must have an implementation");
        }

        // Check for existing macro if overwrite is not allowed
        if (!$this->overwrite) {
            if ($this->condition !== null) {
                if (MacroManager::hasConditionalMacro($this->className, $this->name)) {
                    throw new InvalidArgumentException("Conditional macro '{$this->name}' already exists for class '{$this->className}'");
                }
            } elseif ($this->namespace !== null) {
                if (MacroManager::hasNamespacedMacro($this->namespace, $this->className, $this->name)) {
                    throw new InvalidArgumentException("Namespaced macro '{$this->name}' already exists for class '{$this->className}' in namespace '{$this->namespace}'");
                }
            } else {
                if (MacroManager::hasGlobalMacro($this->className, $this->name)) {
                    throw new InvalidArgumentException("Macro '{$this->name}' already exists for class '{$this->className}'");
                }
            }
        }

        // Register based on type
        if ($this->condition !== null) {
            MacroManager::registerConditional($this->className, $this->name, $this->implementation, $this->condition);
        } elseif ($this->namespace !== null) {
            MacroManager::registerNamespaced($this->namespace, $this->className, $this->name, $this->implementation);
        } else {
            MacroManager::register($this->className, $this->name, $this->implementation);
        }
    }

    /**
     * Simple logging method (placeholder for actual logging implementation)
     *
     * @param string $level The log level
     * @param string $message The log message
     * @param array $context The log context
     * @return void
     */
    private function log(string $level, string $message, array $context = []): void
    {
        // This is a placeholder - in a real implementation, this would
        // integrate with the framework's logging system
        if (function_exists('error_log')) {
            $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
            error_log("[{$level}] {$message}{$contextStr}");
        }
    }
}
