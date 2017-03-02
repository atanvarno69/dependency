<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test;

/** SPL use block. */
use InvalidArgumentException;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atanvarno\Dependency\{
    ClassDefinition, Definition
};

class ClassDefinitionTest extends TestCase
{
    public function testConstructorDefaults()
    {
        $definition = new ClassDefinition(true, TestCase::class);
        $this->assertInstanceOf(Definition::class, $definition);
        $this->assertAttributeEquals(true, 'register', $definition);
        $this->assertAttributeEquals(TestCase::class, 'cargo', $definition);
        $this->assertAttributeEquals([], 'parameters', $definition);
        $this->assertAttributeEquals([], 'methods', $definition);
    }

    public function testConstructorWithParameterList()
    {
        $definition = new ClassDefinition(true, TestCase::class, 'A', 2);
        $this->assertAttributeEquals(['A', 2], 'parameters', $definition);
    }

    public function testConstructorThrowsInvalidArgumentExceptionOnInvalidClassName()
    {
        $this->expectException(InvalidArgumentException::class);
        new ClassDefinition(true, 'invalid name');
    }
}
