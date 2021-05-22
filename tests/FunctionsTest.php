<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Test;

use PHPUnit\Framework\TestCase;
use function Atanvarno\Dependency\{
    entry, factory, object, value
};
use Atanvarno\Dependency\Exception\ConfigurationException;
use Atanvarno\Dependency\Definition\{
    Entry, FactoryDefinition, ObjectDefinition, ValueDefinition
};

class FunctionsTest extends TestCase
{    
    public function testFunctionEntry()
    {
        $result = entry('test');
        $this->assertInstanceOf(Entry::class, $result);
    }

    public function testFunctionFactoryDefault()
    {
        $callable = function () { return; };
        $result = factory($callable);
        $this->assertInstanceOf(FactoryDefinition::class, $result);
        $this->assertTrue($result->isRegistered());
        $this->assertAttributeSame([], 'parameters', $result);
    }

    public function testFunctionFactoryWithParameters()
    {
        $callable = function () { return; };
        $parameters = ['A', new Entry('test'), 1];
        $result = factory($callable, $parameters);
        $this->assertAttributeSame($parameters, 'parameters', $result);
    }

    public function testFunctionFactoryNotRegistered()
    {
        $callable = function () { return; };
        $result = factory($callable, [], false);
        $this->assertFalse($result->isRegistered());
    }

    public function testFunctionObjectDefault()
    {
        $className = TestCase::class;
        $result = object($className);
        $this->assertInstanceOf(ObjectDefinition::class, $result);
        $this->assertTrue($result->isRegistered());
        $this->assertAttributeSame([], 'parameters', $result);
    }

    public function testFunctionObjectWithParameters()
    {
        $className = TestCase::class;
        $parameters = ['A', new Entry('test'), 1];
        $result = object($className, $parameters);
        $this->assertAttributeSame($parameters, 'parameters', $result);
    }

    public function testFunctionObjectNotRegistered()
    {
        $className = TestCase::class;
        $result = object($className, [], false);
        $this->assertFalse($result->isRegistered());
    }

    public function testFunctionObjectThrowsExceptionWithInvalidClassName()
    {
        $this->expectException(ConfigurationException::class);
        object('NotARealObject');
    }

    public function testFunctionValueDefault()
    {
        $value = 'test';
        $result = value($value);
        $this->assertInstanceOf(ValueDefinition::class, $result);
        $this->assertTrue($result->isRegistered());
    }

    public function testFunctionValueNotRegistered()
    {
        $value = 'test';
        $result = value($value, false);
        $this->assertFalse($result->isRegistered());
    }
}
