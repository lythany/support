<?php

declare(strict_types=1);

namespace Lythany\Tests\Support\Macros;

use Lythany\Support\Macros\MacroBuilder;
use Lythany\Support\Macros\MacroManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class MacroBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        MacroManager::flush();
    }

    protected function tearDown(): void
    {
        MacroManager::flush();
    }

    public function testCanCreateBuilder(): void
    {
        $builder = new MacroBuilder('TestClass', 'testMethod');
        $this->assertInstanceOf(MacroBuilder::class, $builder);
    }

    public function testCanImplementMacro(): void
    {
        $builder = new MacroBuilder('TestClass', 'testMethod');
        $builder->implement(function (string $value) {
            return "implemented: {$value}";
        });

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));
        
        $macro = MacroManager::resolve('TestClass', 'testMethod');
        $this->assertSame('implemented: test', $macro('test'));
    }

    public function testCanCreateConditionalMacro(): void
    {
        $builder = new MacroBuilder('TestClass', 'conditionalMethod');
        $builder->implement(function () {
            return 'conditional result';
        });
        $builder->when(function () {
            return true;
        });

        $builder->register();

        $this->assertTrue(MacroManager::hasConditionalMacro('TestClass', 'conditionalMethod'));
        
        $macro = MacroManager::resolveConditional('TestClass', 'conditionalMethod', []);
        $this->assertSame('conditional result', $macro());
    }

    public function testCanCreateNamespacedMacro(): void
    {
        $builder = new MacroBuilder('TestClass', 'namespacedMethod');
        $builder->implement(function () {
            return 'namespaced result';
        });
        $builder->inNamespace('test-namespace');

        $builder->register();

        $this->assertTrue(MacroManager::hasNamespacedMacro('test-namespace', 'TestClass', 'namespacedMethod'));
        
        $macro = MacroManager::resolveNamespaced('test-namespace', 'TestClass', 'namespacedMethod');
        $this->assertSame('namespaced result', $macro());
    }

    public function testCanAddMetadata(): void
    {
        $builder = new MacroBuilder('TestClass', 'metadataMethod');
        $builder->implement(function () {
            return 'result';
        });
        $builder->withMetadata(['author' => 'test', 'version' => '1.0']);
        $builder->addMetadata('category', 'utility');

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'metadataMethod'));
    }

    public function testCanSetDescription(): void
    {
        $builder = new MacroBuilder('TestClass', 'describedMethod');
        $builder->implement(function () {
            return 'result';
        });
        $builder->describe('This is a test method');

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'describedMethod'));
    }

    public function testCanAddTags(): void
    {
        $builder = new MacroBuilder('TestClass', 'taggedMethod');
        $builder->implement(function () {
            return 'result';
        });
        $builder->tag('utility', 'helper', 'string');

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'taggedMethod'));
    }

    public function testCanSetPriority(): void
    {
        $builder = new MacroBuilder('TestClass', 'priorityMethod');
        $builder->implement(function () {
            return 'result';
        });
        $builder->priority(10);

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'priorityMethod'));
    }

    public function testCanCreateChainableMacro(): void
    {
        $builder = new MacroBuilder('TestClass', 'chainableMethod');
        $builder->chainable(function (string $value) {
            // Return null to enable chaining
            return null;
        });

        $builder->register();

        $macro = MacroManager::resolve('TestClass', 'chainableMethod');
        $result = $macro('test');
        
        // Since the chainable macro returns the builder instance when result is null
        $this->assertInstanceOf(MacroBuilder::class, $result);
    }

    public function testCanCreateCachedMacro(): void
    {
        $callCount = 0;
        
        $builder = new MacroBuilder('TestClass', 'cachedMethod');
        $builder->cached(function (string $value) use (&$callCount) {
            $callCount++;
            return "cached: {$value} (call #{$callCount})";
        }, 60); // 60 second TTL

        $builder->register();

        $macro = MacroManager::resolve('TestClass', 'cachedMethod');
        
        // First call
        $result1 = $macro('test');
        $this->assertSame('cached: test (call #1)', $result1);
        
        // Second call should return cached result
        $result2 = $macro('test');
        $this->assertSame('cached: test (call #1)', $result2);
        
        // Verify callback was only called once
        $this->assertSame(1, $callCount);
    }

    public function testCanCreateLoggedMacro(): void
    {
        $builder = new MacroBuilder('TestClass', 'loggedMethod');
        $builder->logged(function (string $value) {
            return "logged: {$value}";
        }, 'info');

        $builder->register();

        $macro = MacroManager::resolve('TestClass', 'loggedMethod');
        $result = $macro('test');
        
        $this->assertSame('logged: test', $result);
    }

    public function testOverwriteProtection(): void
    {
        // Register initial macro
        $builder1 = new MacroBuilder('TestClass', 'protectedMethod');
        $builder1->implement(function () {
            return 'original';
        });
        $builder1->register();

        // Try to register another macro without overwrite
        $builder2 = new MacroBuilder('TestClass', 'protectedMethod');
        $builder2->implement(function () {
            return 'overwrite';
        });
        $builder2->overwrite(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Macro 'protectedMethod' already exists for class 'TestClass'");
        $builder2->register();
    }

    public function testOverwriteAllowed(): void
    {
        // Register initial macro
        $builder1 = new MacroBuilder('TestClass', 'overwriteMethod');
        $builder1->implement(function () {
            return 'original';
        });
        $builder1->register();

        // Overwrite with new macro
        $builder2 = new MacroBuilder('TestClass', 'overwriteMethod');
        $builder2->implement(function () {
            return 'overwritten';
        });
        $builder2->overwrite(true);
        $builder2->register();

        $macro = MacroManager::resolve('TestClass', 'overwriteMethod');
        $this->assertSame('overwritten', $macro());
    }

    public function testValidationRules(): void
    {
        $builder = new MacroBuilder('TestClass', 'validatedMethod');
        $builder->implement(function (string $value) {
            return "validated: {$value}";
        });
        $builder->validateWith([
            'value' => function ($val) {
                return is_string($val) && strlen($val) > 0;
            }
        ]);
        $builder->validateParameter('length', function ($val) {
            return is_int($val) && $val > 0;
        });

        $builder->register();

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'validatedMethod'));
    }

    public function testRequiresImplementation(): void
    {
        $builder = new MacroBuilder('TestClass', 'emptyMethod');
        // Don't set implementation

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Macro 'emptyMethod' must have an implementation");
        $builder->register();
    }

    public function testFluentInterface(): void
    {
        $builder = new MacroBuilder('TestClass', 'fluentMethod');
        
        $result = $builder
            ->implement(function () { return 'fluent'; })
            ->describe('A fluent method')
            ->tag('fluent', 'test')
            ->priority(5)
            ->withMetadata(['type' => 'test']);

        $this->assertSame($builder, $result);
        
        $builder->register();
        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'fluentMethod'));
    }

    public function testConditionalOverwriteProtection(): void
    {
        // Register initial conditional macro
        $builder1 = new MacroBuilder('TestClass', 'conditionalProtected');
        $builder1->implement(function () {
            return 'original';
        });
        $builder1->when(function () {
            return true;
        });
        $builder1->register();

        // Try to register another conditional macro without overwrite
        $builder2 = new MacroBuilder('TestClass', 'conditionalProtected');
        $builder2->implement(function () {
            return 'overwrite';
        });
        $builder2->when(function () {
            return true;
        });
        $builder2->overwrite(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Conditional macro 'conditionalProtected' already exists for class 'TestClass'");
        $builder2->register();
    }

    public function testNamespacedOverwriteProtection(): void
    {
        // Register initial namespaced macro
        $builder1 = new MacroBuilder('TestClass', 'namespacedProtected');
        $builder1->implement(function () {
            return 'original';
        });
        $builder1->inNamespace('test-namespace');
        $builder1->register();

        // Try to register another namespaced macro without overwrite
        $builder2 = new MacroBuilder('TestClass', 'namespacedProtected');
        $builder2->implement(function () {
            return 'overwrite';
        });
        $builder2->inNamespace('test-namespace');
        $builder2->overwrite(false);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Namespaced macro 'namespacedProtected' already exists for class 'TestClass' in namespace 'test-namespace'");
        $builder2->register();
    }

    public function testCachedMacroWithZeroTTL(): void
    {
        $callCount = 0;
        
        $builder = new MacroBuilder('TestClass', 'permanentCached');
        $builder->cached(function (string $value) use (&$callCount) {
            $callCount++;
            return "permanent: {$value} (call #{$callCount})";
        }, 0); // 0 = cache forever

        $builder->register();

        $macro = MacroManager::resolve('TestClass', 'permanentCached');
        
        // Multiple calls should return same cached result
        $result1 = $macro('test');
        $result2 = $macro('test');
        $result3 = $macro('test');
        
        $this->assertSame('permanent: test (call #1)', $result1);
        $this->assertSame('permanent: test (call #1)', $result2);
        $this->assertSame('permanent: test (call #1)', $result3);
        $this->assertSame(1, $callCount);
    }
}
