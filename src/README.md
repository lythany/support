# Lythany Support Component

The Support component provides a comprehensive collection of utility classes, helper functions, and traits that form the foundation of the Lythany framework. These utilities are designed to enhance developer productivity and provide consistent, reliable functionality across all framework components.

## Table of Contents

- [Overview](#overview)
- [Core Utilities](#core-utilities)
- [Macros System](#macros-system)
- [Facades](#facades)
- [Helper Functions](#helper-functions)
- [Installation](#installation)
- [Usage Examples](#usage-examples)
- [Testing](#testing)
- [Changelog](#changelog)

## Overview

The Support component includes:

- **String Utilities**: Comprehensive string manipulation with 68+ methods
- **Array Utilities**: Advanced array operations and manipulation
- **Collection Class**: Fluent, chainable array operations
- **Macros System**: Runtime class extension capabilities
- **Helper Functions**: Global utility functions for common operations
- **Facades**: Static proxy access to underlying classes
- **Traits**: Reusable functionality for classes

## Core Utilities

### Str Class

The `Str` class provides comprehensive string manipulation capabilities:

```php
use Lythany\Support\Str;

// Case conversions
Str::camel('hello_world');        // 'helloWorld'
Str::snake('HelloWorld');         // 'hello_world'
Str::kebab('HelloWorld');         // 'hello-world'
Str::studly('hello_world');       // 'HelloWorld'

// String operations
Str::contains('Hello World', 'World');     // true
Str::startsWith('Hello', 'He');            // true
Str::endsWith('World', 'ld');              // true
Str::length('Hello');                      // 5

// Advanced features
Str::limit('Long text here', 10);          // 'Long text...'
Str::slug('Hello World!');                 // 'hello-world'
Str::random(10);                           // Random string
Str::uuid();                               // UUID v4

// Validation
Str::isEmail('user@example.com');          // true
Str::isUrl('https://example.com');         // true
Str::isJson('{"key": "value"}');           // true
```

### Arr Class

The `Arr` class provides advanced array manipulation:

```php
use Lythany\Support\Arr;

// Dot notation access
Arr::get($array, 'user.name', 'default');
Arr::set($array, 'user.name', 'John');
Arr::has($array, 'user.email');

// Array operations
Arr::flatten($nestedArray);
Arr::pluck($array, 'name');
Arr::where($array, 'active', true);
Arr::shuffle($array);

// Utilities
Arr::isAssoc($array);
Arr::wrap($value);              // Wrap in array if not already
Arr::divide($array);            // Split into keys and values
```

### Collection Class

The `Collection` class provides a fluent interface for array operations:

```php
use Lythany\Support\Collection;

$collection = new Collection([1, 2, 3, 4, 5]);

$result = $collection
    ->filter(fn($item) => $item > 2)
    ->map(fn($item) => $item * 2)
    ->take(2)
    ->values()
    ->toArray();  // [6, 8]

// Advanced operations
$users = new Collection($userArray);
$activeUsers = $users
    ->where('active', true)
    ->sortBy('name')
    ->groupBy('department')
    ->toArray();
```

## Macros System

The Macros system enables runtime class extension without modifying source code:

### Macroable Trait

Add macro functionality to any class:

```php
use Lythany\Support\Traits\Macroable;

class MyClass
{
    use Macroable;
}

// Register a macro
MyClass::macro('customMethod', function ($param) {
    return "Custom: {$param}";
});

// Use the macro
$instance = new MyClass();
echo $instance->customMethod('Hello'); // "Custom: Hello"
```

### MacroManager

Centralized macro management with advanced features:

```php
use Lythany\Support\Macros\MacroManager;

// Global macros
MacroManager::register('MyClass', 'globalMethod', function() {
    return 'Global macro';
});

// Conditional macros
MacroManager::registerConditional(
    'MyClass', 
    'conditionalMethod',
    function() { return 'Conditional'; },
    function($params) { return $params[0] === 'allowed'; }
);

// Namespaced macros
MacroManager::registerNamespaced(
    'admin',
    'MyClass',
    'adminMethod',
    function() { return 'Admin only'; }
);
```

### MacroBuilder

Fluent API for building complex macros:

```php
use Lythany\Support\Macros\MacroManager;

MacroManager::builder('MyClass', 'advancedMethod')
    ->implement(function($data) {
        return "Processed: {$data}";
    })
    ->cached(300)  // Cache for 5 minutes
    ->logged('info')  // Log execution
    ->describe('An advanced macro with caching and logging')
    ->tag('utility', 'cached')
    ->when(function($params) {
        return count($params) > 0;
    })
    ->register();
```

## Facades

Facades provide static access to underlying classes:

```php
use Lythany\Support\Facades\Str;
use Lythany\Support\Facades\Arr;
use Lythany\Support\Facades\Collection;

// Use facades directly
Str::camel('hello_world');
Arr::get($array, 'key.nested');
Collection::make([1, 2, 3])->sum();
```

## Helper Functions

Global helper functions for common operations:

```php
// Data access with dot notation
$name = data_get($user, 'profile.name', 'Unknown');
data_set($user, 'profile.name', 'John Doe');

// Array helpers
$array = array_wrap($value);        // Ensure value is array
$result = array_get($array, 'key'); // Get with dot notation

// String helpers
$camel = str_camel('hello_world');
$slug = str_slug('Hello World!');
$random = str_random(10);

// Collection helpers
$collection = collect([1, 2, 3]);   // Create collection
$result = collect($data)->where('active', true)->pluck('name');
```

## Installation

The Support component is included with the Lythany framework. To use individual components:

```php
// Composer autoload handles all dependencies
require_once 'vendor/autoload.php';

// Use directly
use Lythany\Support\Str;
use Lythany\Support\Arr;
use Lythany\Support\Collection;
```

## Usage Examples

### Real-world String Processing

```php
use Lythany\Support\Str;

// Clean and process user input
$userInput = "  Hello, World! This is a TEST  ";
$processed = Str::of($userInput)
    ->trim()
    ->lower()
    ->title()
    ->slug();  // "hello-world-this-is-a-test"

// Generate secure tokens
$apiKey = Str::random(32);
$uuid = Str::uuid();

// Content processing
$excerpt = Str::limit($longText, 150, '...');
$words = Str::wordCount($article);
```

### Advanced Array Operations

```php
use Lythany\Support\Arr;
use Lythany\Support\Collection;

// Process nested configuration
$config = [
    'database' => [
        'connections' => [
            'mysql' => ['host' => 'localhost', 'port' => 3306],
            'redis' => ['host' => 'localhost', 'port' => 6379]
        ]
    ]
];

$mysqlHost = Arr::get($config, 'database.connections.mysql.host');

// Transform data collections
$users = collect($rawUserData)
    ->filter(fn($user) => $user['active'])
    ->map(fn($user) => [
        'id' => $user['id'],
        'name' => Str::title($user['name']),
        'email' => Str::lower($user['email'])
    ])
    ->sortBy('name')
    ->values();
```

### Dynamic Class Extension

```php
use Lythany\Support\Traits\Macroable;

class ApiClient
{
    use Macroable;
    
    protected $baseUrl;
    
    public function __construct($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }
}

// Add authentication macro
ApiClient::macro('withAuth', function($token) {
    $this->headers['Authorization'] = "Bearer {$token}";
    return $this;
});

// Add logging macro with caching
MacroManager::builder('ApiClient', 'withLogging')
    ->implement(function($logLevel = 'info') {
        $this->logLevel = $logLevel;
        return $this;
    })
    ->cached(60)  // Cache configuration for 1 minute
    ->describe('Enable request/response logging')
    ->register();

// Usage
$client = new ApiClient('https://api.example.com')
    ->withAuth($token)
    ->withLogging('debug');
```

## Testing

The Support component includes comprehensive test coverage:

```bash
# Run all Support tests
./vendor/bin/phpunit tests/Support/

# Run specific component tests
./vendor/bin/phpunit tests/Support/StrTest.php
./vendor/bin/phpunit tests/Support/ArrTest.php
./vendor/bin/phpunit tests/Support/CollectionTest.php
./vendor/bin/phpunit tests/Support/Traits/MacroableTest.php
./vendor/bin/phpunit tests/Support/Macros/
```

**Test Coverage:**
- **String utilities**: 68 tests, 280 assertions
- **Macros system**: 57 tests, 130 assertions
- **Array & Collection**: Comprehensive coverage
- **Helper functions**: Full validation
- **Total**: 100+ tests ensuring reliability

## Performance

The Support component is optimized for performance:

- **String operations**: Optimized for memory efficiency
- **Array operations**: Minimal overhead implementations
- **Macro system**: Cached resolution for repeated calls
- **Collection**: Lazy evaluation where possible
- **Memory usage**: Careful memory management throughout

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history and changes.

## Contributing

When contributing to the Support component:

1. Follow PSR-12 coding standards
2. Add comprehensive tests for new functionality
3. Update documentation for any API changes
4. Ensure backward compatibility
5. Add performance benchmarks for critical paths

## License

The Lythany Support component is open-sourced software licensed under the [MIT license](LICENSE).

