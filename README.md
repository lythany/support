# Lythany Support

A comprehensive collection of PHP utility classes and helper functions designed to accelerate application development. This package provides powerful tools for string manipulation, array operations, collections, and more.

## Features

### String Utilities (`Str` class)
- **68+ methods** for comprehensive string manipulation
- Unicode-aware operations
- Advanced formatting and conversion
- Pattern matching and replacement
- Encoding and decoding utilities

### Array Utilities (`Arr` class)
- **30+ methods** for array manipulation
- Deep array operations
- Filtering and transformation
- Safe access patterns
- Advanced querying capabilities

### Collection Class
- **80+ methods** for powerful data manipulation
- Fluent, chainable interface
- Laravel-compatible API
- Functional programming patterns
- Type-safe operations

### Macro System
- **Dynamic method injection** for extensibility
- Runtime class enhancement
- Plugin architecture support
- Clean API extension patterns

### Facade System
- **Static proxy pattern** for elegant access
- Container-aware resolution
- IDE-friendly method hints
- Consistent API surface

### Helper Functions
- **50+ global helper functions**
- Common operations simplified
- Framework-agnostic utilities
- Optional inclusion

## Installation

Install via Composer:

```bash
composer require lythany/support
```

## Requirements

- PHP 8.2 or higher
- No external dependencies

## Quick Start

### Using Static Classes

```php
use Lythany\Support\Str;
use Lythany\Support\Arr;
use Lythany\Support\Collection;

// String operations
$slug = Str::slug('Hello World!'); // 'hello-world'
$camel = Str::camel('hello_world'); // 'helloWorld'
$words = Str::words('The quick brown fox', 3); // 'The quick brown...'

// Array operations
$result = Arr::get($data, 'user.profile.name', 'Unknown');
$filtered = Arr::where($items, fn($item) => $item['active']);
$grouped = Arr::groupBy($products, 'category');

// Collection operations
$collection = new Collection([1, 2, 3, 4, 5]);
$result = $collection
    ->filter(fn($item) => $item > 2)
    ->map(fn($item) => $item * 2)
    ->values()
    ->toArray(); // [6, 8, 10]
```

### Using Facades

```php
use Lythany\Support\Facades\Str;
use Lythany\Support\Facades\Arr;
use Lythany\Support\Facades\Collection;

// Same operations with facade syntax
$slug = Str::slug('Hello World!');
$result = Arr::get($data, 'path.to.value');
$collection = Collection::make([1, 2, 3]);
```

### Using Helper Functions

Include the helper functions for global access:

```php
require_once 'vendor/lythany/support/src/helpers.php';

// Now use global helpers
$collection = collect([1, 2, 3]);
$escaped = e('<script>alert("xss")</script>');
$value = data_get($array, 'nested.key', 'default');
```

## Advanced Usage

### Extending with Macros

```php
use Lythany\Support\Str;
use Lythany\Support\Macros\MacroManager;

// Add custom methods at runtime
MacroManager::for(Str::class)
    ->register('reverse', function (string $string): string {
        return strrev($string);
    });

// Use the new method
$reversed = Str::reverse('hello'); // 'olleh'
```

### Collection Pipelines

```php
use Lythany\Support\Collection;

$users = new Collection([
    ['name' => 'John', 'age' => 30, 'active' => true],
    ['name' => 'Jane', 'age' => 25, 'active' => false],
    ['name' => 'Bob', 'age' => 35, 'active' => true],
]);

$activeUserNames = $users
    ->where('active', true)
    ->sortBy('age')
    ->pluck('name')
    ->implode(', '); // 'John, Bob'
```

### Type-Safe Operations

```php
use Lythany\Support\Collection;

// Strong typing support
$numbers = new Collection([1, 2, 3, 4, 5]);
$sum = $numbers->sum(); // int: 15

$prices = new Collection([10.50, 25.00, 15.75]);
$total = $prices->sum(); // float: 51.25
```

## Testing

Run the test suite:

```bash
composer test
```

With coverage:

```bash
composer test:coverage
```

## Documentation

Comprehensive documentation is available for each component:

- [String Utilities](src/README.md#string-utilities)
- [Array Utilities](src/README.md#array-utilities) 
- [Collection Class](src/README.md#collection-class)
- [Macro System](src/README.md#macro-system)
- [Facade System](src/README.md#facade-system)

## Performance

This package is optimized for performance:

- **Zero external dependencies**
- **Efficient algorithms** for all operations
- **Memory-conscious** implementation
- **Lazy evaluation** where beneficial

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@lythany.com instead of using the issue tracker.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Credits

- **Lythany Team** - Initial development
- **Community Contributors** - Enhancements and bug fixes

## Support

- **Documentation**: [https://lythany.com/docs/support](https://lythany.com/docs/support)
- **Issues**: [GitHub Issues](https://github.com/lythany/support/issues)
- **Discussions**: [GitHub Discussions](https://github.com/lythany/support/discussions)

---

**Lythany Support** - Accelerating PHP development with powerful utilities.
