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
use Atanvarno\Dependency\Definition\{
    Entry, ResolveParametersTrait
};

class ResolveParametersTraitTest extends TestCase
{
    use CallProtectedMethodTrait;

    private $container, $trait;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->trait = $this->getMockForTrait(ResolveParametersTrait::class);
    }

    public function testResolveParameterPassthrough()
    {
        $parameter = 'test';
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $this->assertSame($parameter, $result);
    }

    public function testResolveParameterEntry()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('test'))
            ->willReturn(100);
        $parameter = new Entry('test');
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $this->assertSame(100, $result);
    }

    public function testResolveParameterRecursiveWithArray()
    {
        $this->container
            ->expects($this->once())
            ->method('get')
            ->with($this->stringContains('test'))
            ->willReturn(100);
        $parameter = [
            'A' => 'testA',
            'B' => [
                'Y' => new Entry('test'),
                'Z' => 500,
            ],
            'C' => 200,
        ];
        $result = $this->callProtectedMethod(
            $this->trait,
            'resolveParameter',
            [$parameter, $this->container]
        );
        $expected = [
            'A' => 'testA',
            'B' => [
                'Y' => 100,
                'Z' => 500,
            ],
            'C' => 200,
        ];
        $this->assertSame($expected, $result);
    }
}
