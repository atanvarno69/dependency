<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Test\Definition;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** PHPUnit use block. */
use PHPUnit\Framework\TestCase;

/** PHPUnit Utility use block. */
use Atanvarno\PHPUnit\CallProtectedMethodTrait;

/** Package use block. */
use Atanvarno\Dependency\{
    Definition,
    Definition\ObjectDefinition,
    Exception\ConfigurationException,
    Exception\RuntimeException
};

class ObjectDefinitionTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $container;

    public function setUp()
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new ObjectDefinition(TestCase::class, [], true);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeSame(TestCase::class, 'className', $result);
        $this->assertAttributeSame([], 'parameters', $result);
        $this->assertAttributeSame(true, 'register', $result);
    }

    public function testConstructorWithFalseRegister()
    {
        $result = new ObjectDefinition(TestCase::class, [], false);
        $this->assertAttributeSame(false, 'register', $result);
    }

    public function testConstructorRejectsInvalidClassName()
    {
        $this->expectException(ConfigurationException::class);
        new ObjectDefinition('InvalidClassName', [], true);
    }

    public function testFactoryMethod()
    {
        $definition = new ObjectDefinition(ObjectDefinitionTest::class, [], true);
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertInstanceOf(ObjectDefinitionTest::class, $result);
    }

    public function testFactoryMethodBubblesExceptions()
    {
        $definition = new ObjectDefinition(
            ObjectExceptionMock::class,
            [],
            true
        );
        $container = $this->createMock(ContainerInterface::class);
        $this->expectException(RuntimeException::class);
        $this->callProtectedMethod($definition, 'factoryMethod', [$container]);
    }
}

class ObjectExceptionMock
{
    public function __construct()
    {
        throw new \Exception();
    }
}
