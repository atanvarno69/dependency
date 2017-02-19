<?php
/**
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency\Test;

/** SPL use block. */
use ReflectionClass;

/** PHPUnit use block */
use PHPUnit\Framework\TestCase;

/** Package use block. */
use Atan\Dependency\Definition;
use Atan\Dependency\DefinitionTrait;

class DefinitionTraitTest extends TestCase
{
    /** @var Definition $mock */
    private $mock;

    public function setUp()
    {
        $this->mock = new Mock;
    }

    public function testGetCargo()
    {
        $this->setProtectedProperty($this->mock, 'cargo', 'cargoValue');
        $result = $this->mock->getCargo();
        $this->assertSame('cargoValue', $result);
    }

    public function testGetMethods()
    {
        $this->setProtectedProperty(
            $this->mock,
            'methods',
            ['name' => [1, 'two', []]]);
        $result = $this->mock->getMethods();
        $this->assertSame(['name' => [1, 'two', []]], $result);
    }

    public function testGetParameters()
    {
        $this->setProtectedProperty($this->mock, 'parameters', ['A', 2, 'C']);
        $result = $this->mock->getParameters();
        $this->assertSame(['A', 2, 'C'], $result);
    }

    public function testGetRegister()
    {
        $this->setProtectedProperty($this->mock, 'register', 'true');
        $result = $this->mock->getRegister();
        $this->assertTrue($result);
    }

    public function testMethod()
    {
        $expected = [
            ['methodName' => []],
            ['methodName' => [], 'otherMethod' => [1, 2, 3]]
        ];
        $result = $this->mock->method('methodName');
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeEquals($expected[0], 'methods', $result);
        $final = $result->method('otherMethod', 1, 2, 3);
        $this->assertAttributeEquals($expected[1], 'methods', $final);
    }

    /**
     * Sets a protected property on a given object via reflection.
     *
     * @param object $object
     * @param string $property
     * @param mixed  $value
     *
     * @return void
     */
    private function setProtectedProperty($object, string $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $reflection_property = $reflection->getProperty($property);
        $reflection_property->setAccessible(true);
        $reflection_property->setValue($object, $value);
    }
}

class Mock implements Definition
{
    use DefinitionTrait;
}