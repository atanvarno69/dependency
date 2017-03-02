<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\{
    Definition, FactoryDefinition
};

class FactoryDefinitionTest extends TestCase
{
    public function testConstructorDefaults()
    {
        $callable = function() { return; };
        $definition = new FactoryDefinition(true, $callable);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertAttributeEquals(true, 'register', $definition);
        $this->assertAttributeEquals($callable, 'cargo', $definition);
        $this->assertAttributeEquals([], 'parameters', $definition);
        $this->assertAttributeEquals([], 'methods', $definition);
    }

    public function testConstructorWithParameterList()
    {
        $callable = function() { return; };
        $definition = new FactoryDefinition(true, $callable, 'A', 2);
        $this->assertAttributeEquals(['A', 2], 'parameters', $definition);
    }
}
