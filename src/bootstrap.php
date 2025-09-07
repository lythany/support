<?php

declare(strict_types=1);

/**
 * Lythany Framework Bootstrap
 * 
 * This file initializes the framework constants and loads essential helper functions.
 */

// Define framework constants
if (!defined('LYTHANY_BASE_PATH')) {
    define('LYTHANY_BASE_PATH', dirname(__DIR__, 3));
}

if (!defined('LYTHANY_START')) {
    define('LYTHANY_START', microtime(true));
}

if (!defined('LYTHANY_VERSION')) {
    define('LYTHANY_VERSION', '1.0.0-dev');
}

// Load helper functions
require_once __DIR__ . '/helpers.php';

// Set up error reporting
if (!function_exists('lythany_error_handler')) {
    /**
     * Custom error handler for Lythany framework
     * 
     * @param int $errno
     * @param string $errstr
     * @param string $errfile
     * @param int $errline
     * @return bool
     */
    function lythany_error_handler(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Don't handle errors that are suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict Notice',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated',
        ];

        $errorType = $errorTypes[$errno] ?? 'Unknown Error';
        
        error_log("[LYTHANY {$errorType}] {$errstr} in {$errfile} on line {$errline}");
        
        // Don't execute PHP internal error handler
        return true;
    }
}

// Set error handler only if not in testing environment
if (!defined('LYTHANY_TESTING')) {
    set_error_handler('lythany_error_handler');
}

// Set up exception handler
if (!function_exists('lythany_exception_handler')) {
    /**
     * Custom exception handler for Lythany framework
     * 
     * @param Throwable $exception
     * @return void
     */
    function lythany_exception_handler(Throwable $exception): void
    {
        $message = sprintf(
            "[LYTHANY EXCEPTION] %s: %s in %s on line %d",
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        error_log($message);
        
        // If we're in debug mode, show detailed error
        if (env('APP_DEBUG', false)) {
            echo "<pre>{$message}\n\nStack trace:\n{$exception->getTraceAsString()}</pre>";
        } else {
            // In production, show generic error
            http_response_code(500);
            echo "Internal Server Error";
        }
        
        exit(1);
    }
}

// Set exception handler only if not in testing environment
if (!defined('LYTHANY_TESTING')) {
    set_exception_handler('lythany_exception_handler');
}

// Load environment variables if .env file exists
if (!function_exists('load_environment')) {
    /**
     * Load environment variables from .env file
     * 
     * @param string $path
     * @return void
     */
    function load_environment(string $path = ''): void
    {
        $envFile = $path ?: base_path('.env');
        
        if (!file_exists($envFile)) {
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (str_starts_with(trim($line), '#')) {
                continue;
            }
            
            // Parse key=value pairs
            if (str_contains($line, '=')) {
                [$key, $value] = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (strlen($value) > 1 && 
                    ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                     (str_starts_with($value, "'") && str_ends_with($value, "'")))) {
                    $value = substr($value, 1, -1);
                }
                
                // Set environment variable if not already set
                if (!isset($_ENV[$key]) && !isset($_SERVER[$key])) {
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                    putenv("{$key}={$value}");
                }
            }
        }
    }
}

// Load environment variables
load_environment();

// Set default timezone
$timezone = env('APP_TIMEZONE', 'UTC');
if ($timezone && function_exists('date_default_timezone_set')) {
    date_default_timezone_set($timezone);
}

// Set memory limit if specified
$memoryLimit = env('APP_MEMORY_LIMIT');
if ($memoryLimit && function_exists('ini_set')) {
    ini_set('memory_limit', $memoryLimit);
}

// Set up autoloader enhancement for better error messages
if (!function_exists('lythany_autoload_handler')) {
    /**
     * Enhanced autoloader with better error messages
     * 
     * @param string $class
     * @return void
     */
    function lythany_autoload_handler(string $class): void
    {
        // This will be called when a class cannot be autoloaded
        $message = "Class '{$class}' not found. ";
        
        // Provide helpful hints based on namespace
        if (str_starts_with($class, 'Lythany\\')) {
            $message .= "This appears to be a Lythany framework class. ";
            $message .= "Make sure the composer autoloader is loaded and the class exists.";
        } elseif (str_starts_with($class, 'App\\')) {
            $message .= "This appears to be an application class. ";
            $message .= "Make sure the class exists in the correct namespace and directory.";
        }
        
        throw new Error($message);
    }
}

// Register the enhanced autoloader
spl_autoload_register('lythany_autoload_handler');

// Framework initialization complete
if (!defined('LYTHANY_BOOTSTRAPPED')) {
    define('LYTHANY_BOOTSTRAPPED', true);
}
