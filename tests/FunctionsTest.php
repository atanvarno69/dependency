<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use function Atanvarno\Dependency\{
    entry, factory, object, value
};
use Atanvarno\Dependency\Definition\{
    Entry, FactoryDefinition, ObjectDefinition, ValueDefinition
};

class FunctionsTest extends TestCase
{    
    public function testFunctionEntry()
    {
        $result = entry('ID');
        $this->assertInstanceOf(Entry::class, $result);
        $this->assertAttributeEquals('ID', 'id', $result);
    }
    
    public function testFunctionFactoryDefaults()
    {
        $callable = function(){return;};
        $result = factory($callable);
        $this->assertInstanceOf(FactoryDefinition::class, $result);
        $this->assertAttributeEquals([], 'actions', $result);
        $this->assertAttributeEquals($callable, 'callable', $result);
        $this->assertAttributeEquals([], 'parameters', $result);
        $this->assertAttributeEquals(true, 'register', $result);
    }
    
    public function testFunctionFactoryWithParameters()
    {
        $callable = function(){return;};
        $parameters = ['A', 'B', 'C', 4];
        $result = factory($callable, $parameters);
        $this->assertAttributeEquals($parameters, 'parameters', $result);
    }
    
    public function testFunctionFactoryWithFalseRegistration()
    {
        $callable = function(){return;};
        $result = factory($callable, [], false);
        $this->assertAttributeEquals(false, 'register', $result);
    }
    
    public function testFunctionObjectDefaults()
    {
        $name = TestCase::class;
        $result = object($name);
        $this->assertInstanceOf(ObjectDefinition::class, $result);
        $this->assertAttributeEquals([], 'actions', $result);
        $this->assertAttributeEquals($name, 'className', $result);
        $this->assertAttributeEquals([], 'parameters', $result);
        $this->assertAttributeEquals(true, 'register', $result);
    }
    
    public function testFunctionObjectWithParameters()
    {
        $name = TestCase::class;
        $parameters = ['A', 'B', 'C', 4];
        $result = object($name, $parameters);
        $this->assertAttributeEquals($parameters, 'parameters', $result);
    }
    
    public function testFunctionObjectWithFalseRegistration()
    {
        $name = TestCase::class;
        $result = object($name, [], false);
        $this->assertAttributeEquals(false, 'register', $result);
    }
    
    public function testFunctionValueDefaults()
    {
        $value = 'A';
        $result = value($value);
        $this->assertInstanceOf(ValueDefinition::class, $result);
        $this->assertAttributeEquals([], 'actions', $result);
        $this->assertAttributeEquals($value, 'value', $result);
        $this->assertAttributeEquals(true, 'register', $result);
    }
    
    public function testFunctionValueWithFalseRegistration()
    {
        $result = value('A', false);
        $this->assertAttributeEquals(false, 'register', $result);
    }
}
