<?php
/**
 * DefinitionTest class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** SPL use block. */
use InvalidArgumentException;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atan\Dependency\Definition;

class DefinitionTest extends TestCase
{
    public function testConstructorDefaults()
    {
        $definition = new Definition(TestCase::class);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertAttributeEquals(TestCase::class, 'className', $definition);
        $this->assertAttributeEquals([], 'parameters', $definition);
        $this->assertAttributeEquals([], 'methods', $definition);
        $this->assertAttributeEquals(true, 'register', $definition);
    }

    public function testConstructorRejectsInvalidClassName()
    {
        $this->expectException(InvalidArgumentException::class);
        new Definition('InvalidClassName');
    }

    public function testConstructorAcceptsParametersList()
    {
        $expected = ['test', 1, [1, 2, 3]];
        $definition = new Definition(TestCase::class, ['test', 1, [1, 2, 3]]);
        $this->assertAttributeEquals($expected, 'parameters', $definition);
    }

    public function testConstructorAcceptsRegistrationFlag()
    {
        $definition = new Definition(TestCase::class, [], false);
        $this->assertAttributeEquals(false, 'register', $definition);
    }

    public function testGetClassName()
    {
        $definition = new Definition(TestCase::class);
        $this->assertSame(TestCase::class, $definition->getClassName());
    }

    public function testGetMethods()
    {
        $expected = ['methodName' => []];
        $definition = new Definition(TestCase::class);
        $definition->method('methodName');
        $this->assertSame($expected, $definition->getMethods());
    }

    public function testGetParameters()
    {
        $expected = ['test', 1, [1, 2, 3]];
        $definition = new Definition(TestCase::class, ['test', 1, [1, 2, 3]]);
        $this->assertSame($expected, $definition->getParameters());
    }

    public function testGetRegister()
    {
        $definition = new Definition(TestCase::class);
        $this->assertSame(true, $definition->getRegister());
    }

    public function testMethod()
    {
        $expected[0] = ['methodName' => []];
        $expected[1] = [
            'methodName' => [],
            'otherMethod' => [1, 2, 3],
        ];
        $definition = new Definition(TestCase::class);
        $result = $definition->method('methodName');
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals($expected[0], 'methods', $result);
        $final = $result->method('otherMethod', [1, 2, 3]);
        $this->assertAttributeEquals($expected[1], 'methods', $final);
    }

    public function testMethods()
    {
        $expected = [
            'methodName' => [],
            'otherMethod' => [1, 2, 3],
        ];
        $definition = new Definition(TestCase::class);
        $result = $definition->methods([
            'methodName' => [],
            'otherMethod' => [1, 2, 3]
        ]);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals($expected, 'methods', $result);
    }

    public function testParameter()
    {
        $expected = [1];
        $definition = new Definition(TestCase::class);
        $result = $definition->parameter(1);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals($expected, 'parameters', $result);
    }

    public function testParameters()
    {
        $expected = ['test', 1, [1, 2, 3]];
        $definition = new Definition(TestCase::class);
        $result = $definition->parameters(['test', 1, [1, 2, 3]]);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals($expected, 'parameters', $result);
    }

    public function testRegister()
    {
        $definition = new Definition(TestCase::class);
        $result = $definition->register(false);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals(false, 'register', $result);
        $final = $result->register();
        $this->assertAttributeEquals(true, 'register', $final);
    }
}
