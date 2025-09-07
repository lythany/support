<?php

declare(strict_types=1);

namespace Lythany\Tests\Support\Traits;

use Lythany\Support\Traits\Macroable;
use PHPUnit\Framework\TestCase;
use Closure;
use BadMethodCallException;

class MacroableTest extends TestCase
{
    private MacroableTestClass $instance;

    protected function setUp(): void
    {
        $this->instance = new MacroableTestClass();
        
        // Clear any existing macros
        MacroableTestClass::flushMacros();
    }

    protected function tearDown(): void
    {
        MacroableTestClass::flushMacros();
    }

    public function testCanRegisterAndCallInstanceMacro(): void
    {
        $this->instance::macro('instanceMethod', function (string $value) {
            return "instance: {$value}";
        });

        $result = $this->instance->instanceMethod('test');
        $this->assertSame('instance: test', $result);
    }

    public function testCanRegisterAndCallStaticMacro(): void
    {
        MacroableTestClass::macro('staticMethod', function (string $value) {
            return "static: {$value}";
        });

        $result = MacroableTestClass::staticMethod('test');
        $this->assertSame('static: test', $result);
    }

    public function testMacroHasAccessToInstanceContext(): void
    {
        $this->instance->testProperty = 'instance_value';

        $this->instance::macro('contextMethod', function () {
            /** @var MacroableTestClass $this */
            return $this->testProperty;
        });

        $result = $this->instance->contextMethod();
        $this->assertSame('instance_value', $result);
    }

    public function testCanCheckIfMacroExists(): void
    {
        $this->assertFalse(MacroableTestClass::hasMacro('nonExistentMethod'));

        MacroableTestClass::macro('existingMethod', function () {
            return 'exists';
        });

        $this->assertTrue(MacroableTestClass::hasMacro('existingMethod'));
    }

    public function testCanFlushAllMacros(): void
    {
        MacroableTestClass::macro('method1', function () {
            return 'method1';
        });

        MacroableTestClass::macro('method2', function () {
            return 'method2';
        });

        $this->assertTrue(MacroableTestClass::hasMacro('method1'));
        $this->assertTrue(MacroableTestClass::hasMacro('method2'));

        MacroableTestClass::flushMacros();

        $this->assertFalse(MacroableTestClass::hasMacro('method1'));
        $this->assertFalse(MacroableTestClass::hasMacro('method2'));
    }

    public function testCanRegisterMixin(): void
    {
        $mixin = new MacroableTestMixin();

        MacroableTestClass::mixin($mixin);

        $this->assertTrue(MacroableTestClass::hasMacro('mixinMethod1'));
        $this->assertTrue(MacroableTestClass::hasMacro('mixinMethod2'));

        $result1 = $this->instance->mixinMethod1();
        $result2 = MacroableTestClass::mixinMethod2();

        $this->assertSame('mixin method 1', $result1);
        $this->assertSame('mixin method 2', $result2);
    }

    public function testMixinReplacesExistingMacros(): void
    {
        MacroableTestClass::macro('mixinMethod1', function () {
            return 'original method';
        });

        $this->assertSame('original method', $this->instance->mixinMethod1());

        $mixin = new MacroableTestMixin();
        MacroableTestClass::mixin($mixin, false); // replace = false, should not replace

        $this->assertSame('original method', $this->instance->mixinMethod1());

        MacroableTestClass::mixin($mixin, true); // replace = true, should replace

        $this->assertSame('mixin method 1', $this->instance->mixinMethod1());
    }

    public function testThrowsExceptionForNonExistentMacro(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Method 'nonExistentMethod' does not exist");

        $this->instance->nonExistentMethod();
    }

    public function testThrowsExceptionForNonExistentStaticMacro(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage("Method 'nonExistentStaticMethod' does not exist");

        MacroableTestClass::nonExistentStaticMethod();
    }

    public function testMacroParametersArePassedCorrectly(): void
    {
        MacroableTestClass::macro('parameterTest', function (string $a, int $b, array $c = []) {
            return [
                'a' => $a,
                'b' => $b,
                'c' => $c,
            ];
        });

        $result = $this->instance->parameterTest('test', 42, ['key' => 'value']);

        $this->assertSame([
            'a' => 'test',
            'b' => 42,
            'c' => ['key' => 'value'],
        ], $result);
    }

    public function testMacroReturnsClosureResult(): void
    {
        MacroableTestClass::macro('returnTest', function () {
            return function (string $value) {
                return "nested: {$value}";
            };
        });

        $result = $this->instance->returnTest();
        $this->assertInstanceOf(Closure::class, $result);
        $this->assertSame('nested: test', $result('test'));
    }

    public function testMacroCanModifyInstanceState(): void
    {
        $this->instance->testProperty = 'initial';

        MacroableTestClass::macro('modifyState', function (string $newValue) {
            /** @var MacroableTestClass $this */
            $this->testProperty = $newValue;
            return $this;
        });

        $returned = $this->instance->modifyState('modified');

        $this->assertSame($this->instance, $returned);
        $this->assertSame('modified', $this->instance->testProperty);
    }

    public function testStaticMacroCannotAccessInstanceContext(): void
    {
        MacroableTestClass::macro('staticContextTest', function () {
            // This should work in static context
            return 'static context';
        });

        $result = MacroableTestClass::staticContextTest();
        $this->assertSame('static context', $result);
    }

    public function testMacrosAreInheritedBySubclasses(): void
    {
        MacroableTestClass::macro('inheritedMethod', function () {
            return 'inherited';
        });

        $subclass = new MacroableTestSubclass();
        $result = $subclass->inheritedMethod();

        $this->assertSame('inherited', $result);
    }

    public function testSubclassCanOverrideParentMacros(): void
    {
        MacroableTestClass::macro('overrideMethod', function () {
            return 'parent';
        });

        MacroableTestSubclass::macro('overrideMethod', function () {
            return 'child';
        });

        $parent = new MacroableTestClass();
        $child = new MacroableTestSubclass();

        $this->assertSame('parent', $parent->overrideMethod());
        $this->assertSame('child', $child->overrideMethod());
    }

    public function testMacrosAreIsolatedBetweenClasses(): void
    {
        MacroableTestClass::macro('classSpecificMethod', function () {
            return 'class1';
        });

        MacroableTestClass2::macro('classSpecificMethod', function () {
            return 'class2';
        });

        $instance1 = new MacroableTestClass();
        $instance2 = new MacroableTestClass2();

        $this->assertSame('class1', $instance1->classSpecificMethod());
        $this->assertSame('class2', $instance2->classSpecificMethod());

        // Verify isolation
        $this->assertTrue(MacroableTestClass::hasMacro('classSpecificMethod'));
        $this->assertTrue(MacroableTestClass2::hasMacro('classSpecificMethod'));

        MacroableTestClass::flushMacros();

        $this->assertFalse(MacroableTestClass::hasMacro('classSpecificMethod'));
        $this->assertTrue(MacroableTestClass2::hasMacro('classSpecificMethod'));
    }
}

// Test classes for Macroable testing
class MacroableTestClass
{
    use Macroable;

    public string $testProperty = '';
}

class MacroableTestSubclass extends MacroableTestClass
{
}

class MacroableTestClass2
{
    use Macroable;
}

class MacroableTestMixin
{
    public function mixinMethod1(): Closure
    {
        return function () {
            return 'mixin method 1';
        };
    }

    public function mixinMethod2(): Closure
    {
        return function () {
            return 'mixin method 2';
        };
    }

    public function nonClosureMethod(): string
    {
        return 'not a closure';
    }
}
