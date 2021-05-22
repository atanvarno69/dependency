<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Test\Definition;

use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Atanvarno\PHPUnit\CallProtectedMethodTrait;
use Atanvarno\Dependency\{
    Definition, Definition\FactoryDefinition, Exception\RuntimeException
};

class FactoryDefinitionTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $callable, $container;

    public function setUp(): void
    {
        $this->callable = function(){return 'test';};
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testConstructor()
    {
        $result = new FactoryDefinition($this->callable, [], true);
        $this->assertInstanceOf(Definition::class, $result);
        $this->assertAttributeSame($this->callable, 'callable', $result);
        $this->assertAttributeSame([], 'parameters', $result);
        $this->assertAttributeSame(true, 'register', $result);
    }

    public function testConstructorWithFalseRegister()
    {
        $result = new FactoryDefinition($this->callable, [], false);
        $this->assertAttributeSame(false, 'register', $result);
    }

    public function testFactoryMethod()
    {
        $definition = new FactoryDefinition($this->callable, [], true);
        $container = $this->createMock(ContainerInterface::class);
        $result = $this->callProtectedMethod(
            $definition,
            'factoryMethod',
            [$container]
        );
        $this->assertSame('test', $result);
    }

    public function testFactoryMethodBubblesExceptions()
    {
        $callable = function(){throw New \Exception();};
        $definition = new FactoryDefinition($callable, [], true);
        $container = $this->createMock(ContainerInterface::class);
        $this->expectException(RuntimeException::class);
        $this->callProtectedMethod($definition, 'factoryMethod', [$container]);
    }
}
