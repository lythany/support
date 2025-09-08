<?php

declare(strict_types=1);

use Lythany\Support\Arr;

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation
     * 
     * @param mixed $target
     * @param string|array|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function data_get(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', (string) $key);

        foreach ($key as $segment) {
            if (is_array($target)) {
                if (array_key_exists($segment, $target)) {
                    $target = $target[$segment];
                } else {
                    return $default;
                }
            } elseif (is_object($target)) {
                if (isset($target->{$segment})) {
                    $target = $target->{$segment};
                } elseif (method_exists($target, $segment)) {
                    $target = $target->{$segment}();
                } else {
                    return $default;
                }
            } else {
                return $default;
            }
        }

        return $target;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation
     * 
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === null) {
            return $target = $value;
        }

        if (!is_array($target)) {
            $target = [];
        }

        if ($segments) {
            data_set($target[$segment], $segments, $value, $overwrite);
        } elseif ($overwrite || !array_key_exists($segment, $target)) {
            $target[$segment] = $value;
        }

        return $target;
    }
}

if (!function_exists('data_fill')) {
    /**
     * Fill in data where it's missing
     * 
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    function data_fill(mixed &$target, string|array $key, mixed $value): mixed
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation
     * 
     * @param array $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function array_get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        return Arr::get($array, $key, $default);
    }
}

if (!function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation
     * 
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    function array_set(array &$array, string $key, mixed $value): array
    {
        return Arr::set($array, $key, $value);
    }
}

if (!function_exists('array_has')) {
    /**
     * Check if an item exists in an array using "dot" notation
     * 
     * @param array $array
     * @param string|array $keys
     * @return bool
     */
    function array_has(array $array, string|array $keys): bool
    {
        $keys = (array) $keys;

        if (empty($array) || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            if (!Arr::has($array, $key)) {
                return false;
            }
        }

        return true;
    }
}

if (!function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation
     * 
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    function array_forget(array &$array, array|string $keys): void
    {
        Arr::forget($array, $keys);
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_only(array $array, array|string $keys): array
    {
        return Arr::only($array, $keys);
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_except(array $array, array|string $keys): array
    {
        return Arr::except($array, $keys);
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array
     * 
     * @param array $array
     * @param string $value
     * @param string|null $key
     * @return array
     */
    function array_pluck(array $array, string $value, ?string $key = null): array
    {
        return Arr::pluck($array, $value, $key);
    }
}

if (!function_exists('array_where')) {
    /**
     * Filter the array using the given callback
     * 
     * @param array $array
     * @param callable $callback
     * @return array
     */
    function array_where(array $array, callable $callback): array
    {
        return Arr::where($array, $callback);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level
     * 
     * @param array $array
     * @param int $depth
     * @return array
     */
    function array_flatten(array $array, int $depth = INF): array
    {
        return Arr::flatten($array, $depth);
    }
}

if (!function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array
     * 
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     */
    function array_prepend(array $array, mixed $value, mixed $key = null): array
    {
        return Arr::prepend($array, $value, $key);
    }
}

if (!function_exists('value')) {
    /**
     * Return the value of the given value
     * 
     * @param mixed $value
     * @param mixed ...$args
     * @return mixed
     */
    function value(mixed $value, mixed ...$args): mixed
    {
        return $value instanceof Closure ? $value(...$args) : $value;
    }
}

if (!function_exists('with')) {
    /**
     * Return the given value, optionally passed through the given callback
     * 
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function with(mixed $value, ?callable $callback = null): mixed
    {
        return $callback === null ? $value : $callback($value);
    }
}

if (!function_exists('tap')) {
    /**
     * Call the given Closure with the given value then return the value
     * 
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function tap(mixed $value, ?callable $callback = null): mixed
    {
        if ($callback !== null) {
            $callback($value);
        }

        return $value;
    }
}

if (!function_exists('optional')) {
    /**
     * Provide access to optional objects
     * 
     * @param mixed $value
     * @param callable|null $callback
     * @return mixed
     */
    function optional(mixed $value = null, ?callable $callback = null): mixed
    {
        if ($callback !== null) {
            return $value !== null ? $callback($value) : null;
        }

        return $value;
    }
}

if (!function_exists('retry')) {
    /**
     * Retry an operation a given number of times
     * 
     * @param int $times
     * @param callable $callback
     * @param int $sleep
     * @param callable|null $when
     * @return mixed
     * @throws Exception
     */
    function retry(int $times, callable $callback, int $sleep = 0, ?callable $when = null): mixed
    {
        $attempts = 0;

        while ($attempts < $times) {
            $attempts++;

            try {
                return $callback($attempts);
            } catch (Exception $e) {
                if ($attempts >= $times || ($when !== null && !$when($e))) {
                    throw $e;
                }

                if ($sleep > 0) {
                    usleep($sleep * 1000);
                }
            }
        }

        throw new Exception('Maximum retry attempts exceeded');
    }
}

if (!function_exists('transform')) {
    /**
     * Transform the given value if it is present
     * 
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return mixed
     */
    function transform(mixed $value, callable $callback, mixed $default = null): mixed
    {
        if (filled($value)) {
            return $callback($value);
        }

        if (is_callable($default)) {
            return $default($value);
        }

        return $default;
    }
}

if (!function_exists('filled')) {
    /**
     * Determine if a value is "filled"
     * 
     * @param mixed $value
     * @return bool
     */
    function filled(mixed $value): bool
    {
        return !blank($value);
    }
}

if (!function_exists('blank')) {
    /**
     * Determine if a value is "blank"
     * 
     * @param mixed $value
     * @return bool
     */
    function blank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if (is_string($value)) {
            return trim($value) === '';
        }

        if (is_numeric($value) || is_bool($value)) {
            return false;
        }

        if ($value instanceof Countable) {
            return count($value) === 0;
        }

        return empty($value);
    }
}

if (!function_exists('class_basename')) {
    /**
     * Get the class "basename" of the given object / class
     * 
     * @param string|object $class
     * @return string
     */
    function class_basename(string|object $class): string
    {
        $class = is_object($class) ? get_class($class) : $class;

        return basename(str_replace('\\', '/', $class));
    }
}

if (!function_exists('trait_uses_recursive')) {
    /**
     * Returns all traits used by a trait and its traits
     * 
     * @param string $trait
     * @return array
     */
    function trait_uses_recursive(string $trait): array
    {
        $traits = class_uses($trait) ?: [];

        foreach ($traits as $trait) {
            $traits += trait_uses_recursive($trait);
        }

        return $traits;
    }
}

if (!function_exists('class_uses_recursive')) {
    /**
     * Returns all traits used by a class, its parent classes and trait of their traits
     * 
     * @param object|string $class
     * @return array
     */
    function class_uses_recursive(object|string $class): array
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];

        foreach (array_reverse(class_parents($class)) + [$class => $class] as $class) {
            $results += trait_uses_recursive($class);
        }

        return array_unique($results);
    }
}

if (!function_exists('env')) {
    /**
     * Get the value of an environment variable
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (strlen($value) > 1 && str_starts_with($value, '"') && str_ends_with($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    /**
     * Get the path to the base of the install
     * 
     * @param string $path
     * @return string
     */
    function base_path(string $path = ''): string
    {
        $basePath = defined('LYTHANY_BASE_PATH') ? LYTHANY_BASE_PATH : getcwd();
        
        return rtrim($basePath, DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
    }
}

if (!function_exists('app_path')) {
    /**
     * Get the path to the application directory
     * 
     * @param string $path
     * @return string
     */
    function app_path(string $path = ''): string
    {
        return base_path('app' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('config_path')) {
    /**
     * Get the path to the configuration directory
     * 
     * @param string $path
     * @return string
     */
    function config_path(string $path = ''): string
    {
        return base_path('config' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('database_path')) {
    /**
     * Get the path to the database directory
     * 
     * @param string $path
     * @return string
     */
    function database_path(string $path = ''): string
    {
        return base_path('database' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('public_path')) {
    /**
     * Get the path to the public directory
     * 
     * @param string $path
     * @return string
     */
    function public_path(string $path = ''): string
    {
        return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('resource_path')) {
    /**
     * Get the path to the resources directory
     * 
     * @param string $path
     * @return string
     */
    function resource_path(string $path = ''): string
    {
        return base_path('resources' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('storage_path')) {
    /**
     * Get the path to the storage directory
     * 
     * @param string $path
     * @return string
     */
    function storage_path(string $path = ''): string
    {
        return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('vendor_path')) {
    /**
     * Get the path to the vendor directory
     * 
     * @param string $path
     * @return string
     */
    function vendor_path(string $path = ''): string
    {
        return base_path('vendor' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
    }
}

if (!function_exists('config')) {
    /**
     * Get / set the specified configuration value
     * 
     * @param array|string|null $key
     * @param mixed $default
     * @return mixed
     */
    function config(array|string|null $key = null, mixed $default = null): mixed
    {
        static $config = null;
        
        if ($config === null) {
            $config = [];
            
            // Load configuration files if they exist
            $configPath = config_path();
            if (is_dir($configPath)) {
                $files = glob($configPath . '/*.php');
                foreach ($files as $file) {
                    $name = basename($file, '.php');
                    $config[$name] = require $file;
                }
            }
        }

        if ($key === null) {
            return $config;
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                data_set($config, $k, $v);
            }
            return $config;
        }

        return data_get($config, $key, $default);
    }
}

if (!function_exists('abort')) {
    /**
     * Throw an HttpException with the given data
     * 
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return never
     */
    function abort(int $code, string $message = '', array $headers = []): never
    {
        throw new RuntimeException($message ?: "HTTP {$code} Error", $code);
    }
}

if (!function_exists('abort_if')) {
    /**
     * Throw an HttpException with the given data if the given condition is true
     * 
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     */
    function abort_if(bool $boolean, int $code, string $message = '', array $headers = []): void
    {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('abort_unless')) {
    /**
     * Throw an HttpException with the given data unless the given condition is true
     * 
     * @param bool $boolean
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     */
    function abort_unless(bool $boolean, int $code, string $message = '', array $headers = []): void
    {
        if (!$boolean) {
            abort($code, $message, $headers);
        }
    }
}

if (!function_exists('now')) {
    /**
     * Create a new Carbon instance for the current time
     * 
     * @param DateTimeZone|string|null $tz
     * @return DateTime
     */
    function now(DateTimeZone|string|null $tz = null): DateTime
    {
        if ($tz instanceof DateTimeZone) {
            return new DateTime('now', $tz);
        }
        
        if (is_string($tz)) {
            return new DateTime('now', new DateTimeZone($tz));
        }
        
        return new DateTime();
    }
}

if (!function_exists('today')) {
    /**
     * Create a new Carbon instance for today
     * 
     * @param DateTimeZone|string|null $tz
     * @return DateTime
     */
    function today(DateTimeZone|string|null $tz = null): DateTime
    {
        $date = now($tz);
        $date->setTime(0, 0, 0);
        return $date;
    }
}

if (!function_exists('collect')) {
    /**
     * Create a collection from the given value
     * 
     * @param mixed $value
     * @return \Lythany\Support\Collection
     */
    function collect(mixed $value = []): \Lythany\Support\Collection
    {
        return new \Lythany\Support\Collection($value);
    }
}

if (!function_exists('dd')) {
    /**
     * Dump the given variables and die
     * 
     * @param mixed ...$vars
     * @return never
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('dump')) {
    /**
     * Dump the given variables
     * 
     * @param mixed ...$vars
     * @return void
     */
    function dump(mixed ...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
    }
}

if (!function_exists('logger')) {
    /**
     * Log a debug message to the logs
     * 
     * @param string|null $message
     * @param array $context
     * @return mixed
     */
    function logger(?string $message = null, array $context = []): mixed
    {
        if ($message === null) {
            // Return logger instance if available
            return null;
        }

        // For now, just use error_log
        error_log($message);
        
        return null;
    }
}

if (!function_exists('info')) {
    /**
     * Log an info message to the logs
     * 
     * @param string $message
     * @param array $context
     * @return void
     */
    function info(string $message, array $context = []): void
    {
        error_log("[INFO] {$message}");
    }
}

if (!function_exists('report')) {
    /**
     * Report an exception
     * 
     * @param Throwable $exception
     * @return void
     */
    function report(Throwable $exception): void
    {
        error_log("[ERROR] " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());
    }
}

if (!function_exists('rescue')) {
    /**
     * Catch a potential exception and return a default value
     * 
     * @param callable $callback
     * @param mixed $rescue
     * @param callable|null $report
     * @return mixed
     */
    function rescue(callable $callback, mixed $rescue = null, ?callable $report = null): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            if ($report !== null) {
                $report($e);
            }

            return value($rescue, $e);
        }
    }
}

if (!function_exists('throw_if')) {
    /**
     * Throw the given exception if the given condition is true
     * 
     * @param bool $condition
     * @param Throwable|string $exception
     * @param mixed ...$parameters
     * @return void
     * @throws Throwable
     */
    function throw_if(bool $condition, Throwable|string $exception, mixed ...$parameters): void
    {
        if ($condition) {
            if (is_string($exception)) {
                throw new $exception(...$parameters);
            }

            throw $exception;
        }
    }
}

if (!function_exists('throw_unless')) {
    /**
     * Throw the given exception unless the given condition is true
     * 
     * @param bool $condition
     * @param Throwable|string $exception
     * @param mixed ...$parameters
     * @return void
     * @throws Throwable
     */
    function throw_unless(bool $condition, Throwable|string $exception, mixed ...$parameters): void
    {
        throw_if(!$condition, $exception, ...$parameters);
    }
}

if (!function_exists('str_slug')) {
    /**
     * Generate a URL friendly "slug" from a given string
     * 
     * @param string $title
     * @param string $separator
     * @param string|null $language
     * @return string
     */
    function str_slug(string $title, string $separator = '-', ?string $language = 'en'): string
    {
        // Convert to lowercase
        $title = strtolower($title);
        
        // Replace non-alphanumeric characters with separator
        $title = preg_replace('/[^a-z0-9]+/', $separator, $title);
        
        // Remove leading/trailing separators
        $title = trim($title, $separator);
        
        // Replace multiple separators with single separator
        $title = preg_replace('/' . preg_quote($separator, '/') . '+/', $separator, $title);
        
        return $title;
    }
}

if (!function_exists('str_limit')) {
    /**
     * Limit the number of characters in a string
     * 
     * @param string $value
     * @param int $limit
     * @param string $end
     * @return string
     */
    function str_limit(string $value, int $limit = 100, string $end = '...'): string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')) . $end;
    }
}

if (!function_exists('str_words')) {
    /**
     * Limit the number of words in a string
     * 
     * @param string $value
     * @param int $words
     * @param string $end
     * @return string
     */
    function str_words(string $value, int $words = 100, string $end = '...'): string
    {
        preg_match('/^\s*+(?:\S++\s*+){1,' . $words . '}/u', $value, $matches);

        if (!isset($matches[0]) || str_word_count($value) === str_word_count($matches[0])) {
            return $value;
        }

        return rtrim($matches[0]) . $end;
    }
}

if (!function_exists('str_random')) {
    /**
     * Generate a more truly "random" alpha-numeric string
     * 
     * @param int $length
     * @return string
     */
    function str_random(int $length = 16): string
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        
        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
}

if (!function_exists('str_replace_array')) {
    /**
     * Replace a given value in the string sequentially with an array
     * 
     * @param string $search
     * @param array $replace
     * @param string $subject
     * @return string
     */
    function str_replace_array(string $search, array $replace, string $subject): string
    {
        $segments = explode($search, $subject);
        $result = array_shift($segments);

        foreach ($segments as $segment) {
            $result .= (array_shift($replace) ?? $search) . $segment;
        }

        return $result;
    }
}

if (!function_exists('str_replace_first')) {
    /**
     * Replace the first occurrence of a given value in the string
     * 
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_first(string $search, string $replace, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}

if (!function_exists('str_replace_last')) {
    /**
     * Replace the last occurrence of a given value in the string
     * 
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_last(string $search, string $replace, string $subject): string
    {
        if ($search === '') {
            return $subject;
        }

        $position = strrpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}

if (!function_exists('str_start')) {
    /**
     * Begin a string with a single instance of a given value
     * 
     * @param string $value
     * @param string $prefix
     * @return string
     */
    function str_start(string $value, string $prefix): string
    {
        $quoted = preg_quote($prefix, '/');

        return $prefix . preg_replace('/^(?:' . $quoted . ')+/u', '', $value);
    }
}

if (!function_exists('str_finish')) {
    /**
     * Cap a string with a single instance of a given value
     * 
     * @param string $value
     * @param string $cap
     * @return string
     */
    function str_finish(string $value, string $cap): string
    {
        $quoted = preg_quote($cap, '/');

        return preg_replace('/(?:' . $quoted . ')+$/u', '', $value) . $cap;
    }
}

if (!function_exists('str_is')) {
    /**
     * Determine if a given string matches a given pattern
     * 
     * @param string|array $pattern
     * @param string $value
     * @return bool
     */
    function str_is(string|array $pattern, string $value): bool
    {
        $patterns = (array) $pattern;

        if (empty($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = (string) $pattern;

            // If the given value is an exact match we can of course return true right
            // from the beginning. Otherwise, we will translate asterisks and do an
            // actual pattern match against the two strings to see if they match.
            if ($pattern === $value) {
                return true;
            }

            $pattern = preg_quote($pattern, '#');

            // Asterisks are translated into zero-or-more regular expression wildcards
            // to make it convenient to check if the strings starts with the given
            // pattern such as "library/*", making any string check convenient.
            $pattern = str_replace('\*', '.*', $pattern);

            if (preg_match('#^' . $pattern . '\z#u', $value) === 1) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('str_contains')) {
    /**
     * Determine if a given string contains a given substring
     * 
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_contains(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('str_starts_with')) {
    /**
     * Determine if a given string starts with a given substring
     * 
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_starts_with(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_starts_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('str_ends_with')) {
    /**
     * Determine if a given string ends with a given substring
     * 
     * @param string $haystack
     * @param string|array $needles
     * @return bool
     */
    function str_ends_with(string $haystack, string|array $needles): bool
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle !== '' && str_ends_with($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('camel_case')) {
    /**
     * Convert a value to camel case
     * 
     * @param string $value
     * @return string
     */
    function camel_case(string $value): string
    {
        return lcfirst(studly_case($value));
    }
}

if (!function_exists('studly_case')) {
    /**
     * Convert a value to studly caps case
     * 
     * @param string $value
     * @return string
     */
    function studly_case(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return str_replace(' ', '', $value);
    }
}

if (!function_exists('snake_case')) {
    /**
     * Convert a string to snake case
     * 
     * @param string $value
     * @param string $delimiter
     * @return string
     */
    function snake_case(string $value, string $delimiter = '_'): string
    {
        if (!ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));
            $value = strtolower(preg_replace('/(.)(?=[A-Z])/u', '$1' . $delimiter, $value));
        }

        return $value;
    }
}

if (!function_exists('kebab_case')) {
    /**
     * Convert a string to kebab case
     * 
     * @param string $value
     * @return string
     */
    function kebab_case(string $value): string
    {
        return snake_case($value, '-');
    }
}

if (!function_exists('title_case')) {
    /**
     * Convert a value to title case
     * 
     * @param string $value
     * @return string
     */
    function title_case(string $value): string
    {
        return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('head')) {
    /**
     * Get the first element of an array
     * 
     * @param array $array
     * @return mixed
     */
    function head(array $array): mixed
    {
        return reset($array);
    }
}

if (!function_exists('last')) {
    /**
     * Get the last element of an array
     * 
     * @param array $array
     * @return mixed
     */
    function last(array $array): mixed
    {
        return end($array);
    }
}

if (!function_exists('array_add')) {
    /**
     * Add an element to an array using "dot" notation if it doesn't exist
     * 
     * @param array $array
     * @param string $key
     * @param mixed $value
     * @return array
     */
    function array_add(array $array, string $key, mixed $value): array
    {
        if (is_null(array_get($array, $key))) {
            array_set($array, $key, $value);
        }

        return $array;
    }
}

if (!function_exists('array_collapse')) {
    /**
     * Collapse an array of arrays into a single array
     * 
     * @param iterable $array
     * @return array
     */
    function array_collapse(iterable $array): array
    {
        $results = [];

        foreach ($array as $values) {
            if (is_object($values) && method_exists($values, 'all')) {
                $values = $values->all();
            } elseif (!is_array($values)) {
                continue;
            }

            $results[] = $values;
        }

        return array_merge([], ...$results);
    }
}

if (!function_exists('array_divide')) {
    /**
     * Divide an array into two arrays: keys and values
     * 
     * @param array $array
     * @return array
     */
    function array_divide(array $array): array
    {
        return [array_keys($array), array_values($array)];
    }
}

if (!function_exists('array_dot')) {
    /**
     * Flatten a multi-dimensional associative array with dots
     * 
     * @param iterable $array
     * @param string $prepend
     * @return array
     */
    function array_dot(iterable $array, string $prepend = ''): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $results = array_merge($results, array_dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}

if (!function_exists('array_except')) {
    /**
     * Get all of the given array except for a specified array of keys
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_except(array $array, array|string $keys): array
    {
        array_forget($array, $keys);

        return $array;
    }
}

if (!function_exists('array_first')) {
    /**
     * Return the first element in an array passing a given truth test
     * 
     * @param iterable $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    function array_first(iterable $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }

            foreach ($array as $item) {
                return $item;
            }
        }

        foreach ($array as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }

        return value($default);
    }
}

if (!function_exists('array_flatten')) {
    /**
     * Flatten a multi-dimensional array into a single level
     * 
     * @param iterable $array
     * @param int $depth
     * @return array
     */
    function array_flatten(iterable $array, int $depth = INF): array
    {
        $result = [];

        foreach ($array as $item) {
            $item = (is_object($item) && method_exists($item, 'all')) ? $item->all() : $item;

            if (!is_array($item)) {
                $result[] = $item;
            } else {
                $values = $depth === 1
                    ? array_values($item)
                    : array_flatten($item, $depth - 1);

                foreach ($values as $value) {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }
}

if (!function_exists('array_forget')) {
    /**
     * Remove one or many array items from a given array using "dot" notation
     * 
     * @param array $array
     * @param array|string $keys
     * @return void
     */
    function array_forget(array &$array, array|string $keys): void
    {
        $original = &$array;

        $keys = (array) $keys;

        if (count($keys) === 0) {
            return;
        }

        foreach ($keys as $key) {
            // If the exact key exists in the top-level, remove it
            if (array_key_exists($key, $array)) {
                unset($array[$key]);

                continue;
            }

            $parts = explode('.', $key);

            // Clean up before each pass
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
}

if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation
     * 
     * @param ArrayAccess|array $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function array_get(ArrayAccess|array $array, string|int|null $key, mixed $default = null): mixed
    {
        if (!accessible($array)) {
            return value($default);
        }

        if (is_null($key)) {
            return $array;
        }

        if (exists($array, $key)) {
            return $array[$key];
        }

        if (!str_contains($key, '.')) {
            return $array[$key] ?? value($default);
        }

        foreach (explode('.', $key) as $segment) {
            if (accessible($array) && exists($array, $segment)) {
                $array = $array[$segment];
            } else {
                return value($default);
            }
        }

        return $array;
    }
}

if (!function_exists('array_has')) {
    /**
     * Check if an item or items exist in an array using "dot" notation
     * 
     * @param ArrayAccess|array $array
     * @param string|array $keys
     * @return bool
     */
    function array_has(ArrayAccess|array $array, string|array $keys): bool
    {
        $keys = (array) $keys;

        if (!$array || $keys === []) {
            return false;
        }

        foreach ($keys as $key) {
            $subKeyArray = $array;

            if (exists($array, $key)) {
                continue;
            }

            foreach (explode('.', $key) as $segment) {
                if (accessible($subKeyArray) && exists($subKeyArray, $segment)) {
                    $subKeyArray = $subKeyArray[$segment];
                } else {
                    return false;
                }
            }
        }

        return true;
    }
}

if (!function_exists('array_last')) {
    /**
     * Return the last element in an array passing a given truth test
     * 
     * @param array $array
     * @param callable|null $callback
     * @param mixed $default
     * @return mixed
     */
    function array_last(array $array, ?callable $callback = null, mixed $default = null): mixed
    {
        if (is_null($callback)) {
            return empty($array) ? value($default) : end($array);
        }

        return array_first(array_reverse($array, true), $callback, $default);
    }
}

if (!function_exists('array_only')) {
    /**
     * Get a subset of the items from the given array
     * 
     * @param array $array
     * @param array|string $keys
     * @return array
     */
    function array_only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }
}

if (!function_exists('array_pluck')) {
    /**
     * Pluck an array of values from an array
     * 
     * @param iterable $array
     * @param string|array|int|null $value
     * @param string|array|null $key
     * @return array
     */
    function array_pluck(iterable $array, string|array|int|null $value, string|array|null $key = null): array
    {
        $results = [];

        [$value, $key] = explode_pluck_parameters($value, $key);

        foreach ($array as $item) {
            $itemValue = data_get($item, $value);

            // If the key is "null", we will just append the value to the array and keep
            // looping. Otherwise we will key the array using the value of the key we
            // received from the developer. Then we'll return the final array form.
            if (is_null($key)) {
                $results[] = $itemValue;
            } else {
                $itemKey = data_get($item, $key);

                if (is_object($itemKey) && method_exists($itemKey, '__toString')) {
                    $itemKey = (string) $itemKey;
                }

                $results[$itemKey] = $itemValue;
            }
        }

        return $results;
    }
}

if (!function_exists('array_prepend')) {
    /**
     * Push an item onto the beginning of an array
     * 
     * @param array $array
     * @param mixed $value
     * @param mixed $key
     * @return array
     */
    function array_prepend(array $array, mixed $value, mixed $key = null): array
    {
        if (func_num_args() == 2) {
            array_unshift($array, $value);
        } else {
            $array = [$key => $value] + $array;
        }

        return $array;
    }
}

if (!function_exists('array_pull')) {
    /**
     * Get a value from the array, and remove it
     * 
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function array_pull(array &$array, string $key, mixed $default = null): mixed
    {
        $value = array_get($array, $key, $default);

        array_forget($array, $key);

        return $value;
    }
}

if (!function_exists('array_random')) {
    /**
     * Get one or a specified number of random values from an array
     * 
     * @param array $array
     * @param int|null $num
     * @return mixed
     */
    function array_random(array $array, ?int $num = null): mixed
    {
        $requested = is_null($num) ? 1 : $num;

        $count = count($array);

        if ($requested > $count) {
            throw new InvalidArgumentException(
                "You requested {$requested} items, but there are only {$count} items available."
            );
        }

        if (is_null($num)) {
            return $array[array_rand($array)];
        }

        if ((int) $num === 0) {
            return [];
        }

        $keys = array_rand($array, $num);

        $results = [];

        foreach ((array) $keys as $key) {
            $results[] = $array[$key];
        }

        return $results;
    }
}

if (!function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation
     * 
     * @param array $array
     * @param string|null $key
     * @param mixed $value
     * @return array
     */
    function array_set(array &$array, ?string $key, mixed $value): array
    {
        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }
}

if (!function_exists('array_sort')) {
    /**
     * Sort the array using the given callback or "dot" notation
     * 
     * @param array $array
     * @param callable|string|null $callback
     * @return array
     */
    function array_sort(array $array, callable|string|null $callback = null): array
    {
        return collect($array)->sortBy($callback)->all();
    }
}

if (!function_exists('array_sort_recursive')) {
    /**
     * Recursively sort an array by keys and values
     * 
     * @param array $array
     * @param int $options
     * @param bool $descending
     * @return array
     */
    function array_sort_recursive(array $array, int $options = SORT_REGULAR, bool $descending = false): array
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = array_sort_recursive($value, $options, $descending);
            }
        }

        if (!array_is_list($array)) {
            $descending
                ? krsort($array, $options)
                : ksort($array, $options);
        } else {
            $descending
                ? rsort($array, $options)
                : sort($array, $options);
        }

        return $array;
    }
}

if (!function_exists('array_where')) {
    /**
     * Filter the array using the given callback
     * 
     * @param array $array
     * @param callable $callback
     * @return array
     */
    function array_where(array $array, callable $callback): array
    {
        return array_filter($array, $callback, ARRAY_FILTER_USE_BOTH);
    }
}

if (!function_exists('array_wrap')) {
    /**
     * If the given value is not an array and not null, wrap it in one
     * 
     * @param mixed $value
     * @return array
     */
    function array_wrap(mixed $value): array
    {
        if (is_null($value)) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }
}

if (!function_exists('data_fill')) {
    /**
     * Fill in data where it's missing
     * 
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    function data_fill(mixed &$target, string|array $key, mixed $value): mixed
    {
        return data_set($target, $key, $value, false);
    }
}

if (!function_exists('data_get')) {
    /**
     * Get an item from an array or object using "dot" notation
     * 
     * @param mixed $target
     * @param string|array|int|null $key
     * @param mixed $default
     * @return mixed
     */
    function data_get(mixed $target, string|array|int|null $key, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $target;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        foreach ($key as $i => $segment) {
            unset($key[$i]);

            if (is_null($segment)) {
                return $target;
            }

            if ($segment === '*') {
                if (is_object($target) && method_exists($target, 'all')) {
                    $target = $target->all();
                } elseif (!is_iterable($target)) {
                    return value($default);
                }

                $result = [];

                foreach ($target as $item) {
                    $result[] = data_get($item, $key);
                }

                return in_array('*', $key) ? array_collapse($result) : $result;
            }

            if (accessible($target) && exists($target, $segment)) {
                $target = $target[$segment];
            } elseif (is_object($target) && isset($target->{$segment})) {
                $target = $target->{$segment};
            } else {
                return value($default);
            }
        }

        return $target;
    }
}

if (!function_exists('data_set')) {
    /**
     * Set an item on an array or object using dot notation
     * 
     * @param mixed $target
     * @param string|array $key
     * @param mixed $value
     * @param bool $overwrite
     * @return mixed
     */
    function data_set(mixed &$target, string|array $key, mixed $value, bool $overwrite = true): mixed
    {
        $segments = is_array($key) ? $key : explode('.', $key);

        if (($segment = array_shift($segments)) === '*') {
            if (!accessible($target)) {
                $target = [];
            }

            if ($segments) {
                foreach ($target as &$inner) {
                    data_set($inner, $segments, $value, $overwrite);
                }
            } elseif ($overwrite) {
                foreach ($target as &$inner) {
                    $inner = $value;
                }
            }
        } elseif (accessible($target)) {
            if ($segments) {
                if (!exists($target, $segment)) {
                    $target[$segment] = [];
                }

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite || !exists($target, $segment)) {
                $target[$segment] = $value;
            }
        } elseif (is_object($target)) {
            if ($segments) {
                if (!isset($target->{$segment})) {
                    $target->{$segment} = [];
                }

                data_set($target->{$segment}, $segments, $value, $overwrite);
            } elseif ($overwrite || !isset($target->{$segment})) {
                $target->{$segment} = $value;
            }
        } else {
            $target = [];

            if ($segments) {
                $target[$segment] = [];

                data_set($target[$segment], $segments, $value, $overwrite);
            } elseif ($overwrite) {
                $target[$segment] = $value;
            }
        }

        return $target;
    }
}

// Helper functions for array operations
if (!function_exists('accessible')) {
    /**
     * Determine whether the given value is array accessible
     * 
     * @param mixed $value
     * @return bool
     */
    function accessible(mixed $value): bool
    {
        return is_array($value) || $value instanceof ArrayAccess;
    }
}

if (!function_exists('exists')) {
    /**
     * Determine if the given key exists in the provided array
     * 
     * @param ArrayAccess|array $array
     * @param string|int $key
     * @return bool
     */
    function exists(ArrayAccess|array $array, string|int $key): bool
    {
        if ($array instanceof ArrayAccess) {
            return $array->offsetExists($key);
        }

        return array_key_exists($key, $array);
    }
}

if (!function_exists('explode_pluck_parameters')) {
    /**
     * Explode the "value" and "key" arguments passed to "pluck"
     * 
     * @param string|array $value
     * @param string|array|null $key
     * @return array
     */
    function explode_pluck_parameters(string|array $value, string|array|null $key): array
    {
        $value = is_string($value) ? explode('.', $value) : $value;

        $key = is_null($key) || is_array($key) ? $key : explode('.', $key);

        return [$value, $key];
    }
}

if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
        return $factory->make($view, $data, $mergeData);
    }
}

if (!function_exists('e')) {
    /**
     * Escape HTML special characters in a string.
     *
     * @param string $value
     * @param bool $doubleEncode
     * @return string
     */
    function e(string $value, bool $doubleEncode = true): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return string
     */
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    function csrf_token(): string
    {
        // For now, generate a simple token - in full implementation this would
        // integrate with session management
        return bin2hex(random_bytes(32));
    }
}

if (!function_exists('method_field')) {
    /**
     * Generate a form field to spoof the HTTP verb.
     *
     * @param string $method
     * @return string
     */
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . $method . '">';
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve an old input item.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    function old(?string $key = null, mixed $default = null): mixed
    {
        // For now, return default - in full implementation this would
        // integrate with session flash data
        return $default;
    }
}

if (!function_exists('asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    function asset(string $path, ?bool $secure = null): string
    {
        // Simple asset helper - in full implementation this would handle
        // asset versioning, CDN urls, etc.
        return '/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    /**
     * Generate a url for the application.
     *
     * @param string $path
     * @param array $parameters
     * @param bool|null $secure
     * @return string
     */
    function url(string $path = '', array $parameters = [], ?bool $secure = null): string
    {
        // Simple URL helper - in full implementation this would integrate
        // with proper URL generation
        $query = !empty($parameters) ? '?' . http_build_query($parameters) : '';
        return '/' . ltrim($path, '/') . $query;
    }
}

if (!function_exists('route')) {
    /**
     * Generate the URL to a named route.
     *
     * @param string $name
     * @param array $parameters
     * @param bool $absolute
     * @return string
     */
    function route(string $name, array $parameters = [], bool $absolute = true): string
    {
        // Simple route helper - in full implementation this would integrate
        // with the routing system
        return url($name, $parameters);
    }
}

if (!function_exists('secure_asset')) {
    /**
     * Generate an asset path for the application.
     *
     * @param string $path
     * @return string
     */
    function secure_asset(string $path): string
    {
        return asset($path, true);
    }
}

if (!function_exists('secure_url')) {
    /**
     * Generate a HTTPS url for the application.
     *
     * @param string $path
     * @param array $parameters
     * @return string
     */
    function secure_url(string $path, array $parameters = []): string
    {
        return url($path, $parameters, true);
    }
}

// Security helper functions
if (!function_exists('secure_random')) {
    /**
     * Generate a cryptographically secure random string
     *
     * @param int $length
     * @return string
     */
    function secure_random(int $length = 32): string
    {
        return \Lythany\Support\Security::randomString($length);
    }
}

if (!function_exists('hash_password')) {
    /**
     * Hash a password securely
     *
     * @param string $password
     * @return string
     */
    function hash_password(string $password): string
    {
        return \Lythany\Support\Security::hashPassword($password);
    }
}

if (!function_exists('verify_password')) {
    /**
     * Verify a password against its hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    function verify_password(string $password, string $hash): bool
    {
        return \Lythany\Support\Security::verifyPassword($password, $hash);
    }
}

if (!function_exists('sanitize_input')) {
    /**
     * Sanitize user input to prevent XSS
     *
     * @param string $input
     * @param bool $preserveLineBreaks
     * @return string
     */
    function sanitize_input(string $input, bool $preserveLineBreaks = false): string
    {
        return \Lythany\Support\Security::sanitizeInput($input, $preserveLineBreaks);
    }
}

if (!function_exists('validate_email')) {
    /**
     * Validate email address
     *
     * @param string $email
     * @param bool $checkMxRecord
     * @return bool
     */
    function validate_email(string $email, bool $checkMxRecord = false): bool
    {
        return \Lythany\Support\Validator::email($email, $checkMxRecord);
    }
}

if (!function_exists('validate_url')) {
    /**
     * Validate URL with security considerations
     *
     * @param string $url
     * @param array<string> $allowedSchemes
     * @return bool
     */
    function validate_url(string $url, array $allowedSchemes = ['http', 'https']): bool
    {
        return \Lythany\Support\Validator::url($url, $allowedSchemes);
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Generate a CSRF token
     *
     * @return string
     */
    function csrf_token(): string
    {
        return \Lythany\Support\Security::csrfToken();
    }
}

if (!function_exists('secure_hash')) {
    /**
     * Generate a secure hash of data
     *
     * @param string $data
     * @param string $algorithm
     * @return string
     */
    function secure_hash(string $data, string $algorithm = 'sha256'): string
    {
        return \Lythany\Support\Security::hashData($data, $algorithm);
    }
}
