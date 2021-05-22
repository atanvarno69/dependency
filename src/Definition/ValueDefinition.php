<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

use Psr\Container\ContainerInterface as Container;
use Atanvarno\Dependency\Definition;

/**
 * Definition for an arbitrary value container entry.
 *
 * Returned by the helper function `value()`.
 */
class ValueDefinition implements Definition
{
    use DefinitionTrait;
    
    public function __construct(private mixed $value, private bool $register)
    {}

    /** @inheritdoc */
    protected function factoryMethod(Container $container): mixed
    {
        return $this->value;
    }
}
