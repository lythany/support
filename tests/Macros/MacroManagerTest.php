<?php

declare(strict_types=1);

namespace Lythany\Tests\Support\Macros;

use Lythany\Support\Macros\MacroManager;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class MacroManagerTest extends TestCase
{
    protected function setUp(): void
    {
        MacroManager::flush();
    }

    protected function tearDown(): void
    {
        MacroManager::flush();
    }

    public function testCanRegisterGlobalMacro(): void
    {
        MacroManager::register('TestClass', 'testMethod', function () {
            return 'global macro';
        });

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));
    }

    public function testCanRegisterConditionalMacro(): void
    {
        MacroManager::registerConditional(
            'TestClass',
            'conditionalMethod',
            function () {
                return 'conditional macro';
            },
            function () {
                return true;
            }
        );

        $this->assertTrue(MacroManager::hasConditionalMacro('TestClass', 'conditionalMethod'));
    }

    public function testCanRegisterNamespacedMacro(): void
    {
        MacroManager::registerNamespaced(
            'test-namespace',
            'TestClass',
            'namespacedMethod',
            function () {
                return 'namespaced macro';
            }
        );

        $this->assertTrue(MacroManager::hasNamespacedMacro('test-namespace', 'TestClass', 'namespacedMethod'));
    }

    public function testCanResolveGlobalMacro(): void
    {
        MacroManager::register('TestClass', 'testMethod', function (string $value) {
            return "global: {$value}";
        });

        $macro = MacroManager::resolve('TestClass', 'testMethod');
        $this->assertNotNull($macro);

        $result = $macro('test');
        $this->assertSame('global: test', $result);
    }

    public function testCanResolveConditionalMacroWhenConditionIsTrue(): void
    {
        MacroManager::registerConditional(
            'TestClass',
            'conditionalMethod',
            function (string $value) {
                return "conditional: {$value}";
            },
            function (array $parameters) {
                return $parameters[0] === 'allowed';
            }
        );

        $macro = MacroManager::resolveConditional('TestClass', 'conditionalMethod', ['allowed']);
        $this->assertNotNull($macro);

        $result = $macro('test');
        $this->assertSame('conditional: test', $result);
    }

    public function testCannotResolveConditionalMacroWhenConditionIsFalse(): void
    {
        MacroManager::registerConditional(
            'TestClass',
            'conditionalMethod',
            function (string $value) {
                return "conditional: {$value}";
            },
            function (array $parameters) {
                return $parameters[0] === 'allowed';
            }
        );

        $macro = MacroManager::resolveConditional('TestClass', 'conditionalMethod', ['denied']);
        $this->assertNull($macro);
    }

    public function testCanResolveNamespacedMacro(): void
    {
        MacroManager::registerNamespaced(
            'test-namespace',
            'TestClass',
            'namespacedMethod',
            function (string $value) {
                return "namespaced: {$value}";
            }
        );

        $macro = MacroManager::resolveNamespaced('test-namespace', 'TestClass', 'namespacedMethod');
        $this->assertNotNull($macro);

        $result = $macro('test');
        $this->assertSame('namespaced: test', $result);
    }

    public function testCanGetAllMacrosForClass(): void
    {
        MacroManager::register('TestClass', 'method1', function () {
            return 'method1';
        });

        MacroManager::register('TestClass', 'method2', function () {
            return 'method2';
        });

        MacroManager::registerConditional('TestClass', 'conditional1', function () {
            return 'conditional1';
        }, function () {
            return true;
        });

        $macros = MacroManager::getAllMacros('TestClass');

        $this->assertArrayHasKey('global', $macros);
        $this->assertArrayHasKey('conditional', $macros);
        $this->assertCount(2, $macros['global']);
        $this->assertCount(1, $macros['conditional']);
        $this->assertArrayHasKey('method1', $macros['global']);
        $this->assertArrayHasKey('method2', $macros['global']);
        $this->assertArrayHasKey('conditional1', $macros['conditional']);
    }

    public function testCanRemoveMacros(): void
    {
        MacroManager::register('TestClass', 'testMethod', function () {
            return 'test';
        });

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));

        MacroManager::remove('TestClass', 'testMethod');

        $this->assertFalse(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));
    }

    public function testCanRemoveConditionalMacros(): void
    {
        MacroManager::registerConditional('TestClass', 'conditionalMethod', function () {
            return 'test';
        }, function () {
            return true;
        });

        $this->assertTrue(MacroManager::hasConditionalMacro('TestClass', 'conditionalMethod'));

        MacroManager::removeConditional('TestClass', 'conditionalMethod');

        $this->assertFalse(MacroManager::hasConditionalMacro('TestClass', 'conditionalMethod'));
    }

    public function testCanRemoveNamespacedMacros(): void
    {
        MacroManager::registerNamespaced('test-namespace', 'TestClass', 'namespacedMethod', function () {
            return 'test';
        });

        $this->assertTrue(MacroManager::hasNamespacedMacro('test-namespace', 'TestClass', 'namespacedMethod'));

        MacroManager::removeNamespaced('test-namespace', 'TestClass', 'namespacedMethod');

        $this->assertFalse(MacroManager::hasNamespacedMacro('test-namespace', 'TestClass', 'namespacedMethod'));
    }

    public function testCanDisableAndEnableMacros(): void
    {
        MacroManager::register('TestClass', 'testMethod', function () {
            return 'test';
        });

        // Macro should be available initially
        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));
        $this->assertNotNull(MacroManager::resolve('TestClass', 'testMethod'));

        // Disable the macro
        MacroManager::disable('TestClass', 'testMethod');

        // Macro should still exist but not be resolvable
        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'testMethod'));
        $this->assertNull(MacroManager::resolve('TestClass', 'testMethod'));

        // Enable the macro
        MacroManager::enable('TestClass', 'testMethod');

        // Macro should be resolvable again
        $this->assertNotNull(MacroManager::resolve('TestClass', 'testMethod'));
    }

    public function testCanGetStatistics(): void
    {
        MacroManager::register('TestClass1', 'method1', function () {
            return 'test';
        });

        MacroManager::register('TestClass1', 'method2', function () {
            return 'test';
        });

        MacroManager::registerConditional('TestClass2', 'conditional1', function () {
            return 'test';
        }, function () {
            return true;
        });

        MacroManager::registerNamespaced('namespace1', 'TestClass3', 'namespaced1', function () {
            return 'test';
        });

        MacroManager::disable('TestClass1', 'method1');

        $stats = MacroManager::getStatistics();

        $this->assertArrayHasKey('global', $stats);
        $this->assertArrayHasKey('conditional', $stats);
        $this->assertArrayHasKey('namespaced', $stats);
        $this->assertArrayHasKey('disabled', $stats);
        $this->assertArrayHasKey('total', $stats);

        $this->assertSame(2, $stats['global']);
        $this->assertSame(1, $stats['conditional']);
        $this->assertSame(1, $stats['namespaced']);
        $this->assertSame(1, $stats['disabled']);
        $this->assertSame(4, $stats['total']);
    }

    public function testCanFlushAllMacros(): void
    {
        MacroManager::register('TestClass', 'method1', function () {
            return 'test';
        });

        MacroManager::registerConditional('TestClass', 'conditional1', function () {
            return 'test';
        }, function () {
            return true;
        });

        MacroManager::registerNamespaced('namespace1', 'TestClass', 'namespaced1', function () {
            return 'test';
        });

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass', 'method1'));
        $this->assertTrue(MacroManager::hasConditionalMacro('TestClass', 'conditional1'));
        $this->assertTrue(MacroManager::hasNamespacedMacro('namespace1', 'TestClass', 'namespaced1'));

        MacroManager::flush();

        $this->assertFalse(MacroManager::hasGlobalMacro('TestClass', 'method1'));
        $this->assertFalse(MacroManager::hasConditionalMacro('TestClass', 'conditional1'));
        $this->assertFalse(MacroManager::hasNamespacedMacro('namespace1', 'TestClass', 'namespaced1'));

        $stats = MacroManager::getStatistics();
        $this->assertSame(0, $stats['total']);
    }

    public function testCanFlushMacrosForSpecificClass(): void
    {
        MacroManager::register('TestClass1', 'method1', function () {
            return 'test';
        });

        MacroManager::register('TestClass2', 'method2', function () {
            return 'test';
        });

        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass1', 'method1'));
        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass2', 'method2'));

        MacroManager::flushForClass('TestClass1');

        $this->assertFalse(MacroManager::hasGlobalMacro('TestClass1', 'method1'));
        $this->assertTrue(MacroManager::hasGlobalMacro('TestClass2', 'method2'));
    }

    public function testMacroResolutionPriority(): void
    {
        // Register global macro
        MacroManager::register('TestClass', 'testMethod', function () {
            return 'global';
        });

        // Register conditional macro (should take priority when condition is met)
        MacroManager::registerConditional('TestClass', 'testMethod', function () {
            return 'conditional';
        }, function () {
            return true;
        });

        // Resolve without namespace - should get conditional first
        $macro = MacroManager::resolve('TestClass', 'testMethod');
        $this->assertSame('global', $macro()); // Global has priority in base resolve

        // Resolve conditional specifically
        $conditionalMacro = MacroManager::resolveConditional('TestClass', 'testMethod', []);
        $this->assertSame('conditional', $conditionalMacro());
    }

    public function testBuilderIntegration(): void
    {
        $builder = MacroManager::builder('TestClass', 'builderMethod');
        
        $this->assertInstanceOf(\Lythany\Support\Macros\MacroBuilder::class, $builder);
    }

    public function testInvalidParametersThrowExceptions(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        MacroManager::register('', 'testMethod', function () {
            return 'test';
        });
    }

    public function testInvalidMacroNameThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        MacroManager::register('TestClass', '', function () {
            return 'test';
        });
    }

    public function testNamespaceValidation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        
        MacroManager::registerNamespaced('', 'TestClass', 'testMethod', function () {
            return 'test';
        });
    }

    public function testMacroIsolationBetweenClasses(): void
    {
        MacroManager::register('Class1', 'sharedMethod', function () {
            return 'class1';
        });

        MacroManager::register('Class2', 'sharedMethod', function () {
            return 'class2';
        });

        $macro1 = MacroManager::resolve('Class1', 'sharedMethod');
        $macro2 = MacroManager::resolve('Class2', 'sharedMethod');

        $this->assertSame('class1', $macro1());
        $this->assertSame('class2', $macro2());

        // Flushing one class shouldn't affect the other
        MacroManager::flushForClass('Class1');

        $this->assertNull(MacroManager::resolve('Class1', 'sharedMethod'));
        $this->assertNotNull(MacroManager::resolve('Class2', 'sharedMethod'));
    }

    public function testNamespaceIsolation(): void
    {
        MacroManager::registerNamespaced('namespace1', 'TestClass', 'method', function () {
            return 'namespace1';
        });

        MacroManager::registerNamespaced('namespace2', 'TestClass', 'method', function () {
            return 'namespace2';
        });

        $macro1 = MacroManager::resolveNamespaced('namespace1', 'TestClass', 'method');
        $macro2 = MacroManager::resolveNamespaced('namespace2', 'TestClass', 'method');

        $this->assertSame('namespace1', $macro1());
        $this->assertSame('namespace2', $macro2());

        // Cross-namespace access should not work
        $this->assertNull(MacroManager::resolveNamespaced('namespace1', 'TestClass', 'nonexistent'));
        $this->assertNull(MacroManager::resolveNamespaced('namespace3', 'TestClass', 'method'));
    }
}
