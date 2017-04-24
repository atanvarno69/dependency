<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

/** PSR-11 use block. */
use Psr\Container\ContainerInterface;

/** Package use block. */
use Atanvarno\Dependency\Definition;

class ValueDefinition implements Definition
{
    use DefinitionTrait;

    private $value;
    
    public function __construct($value, bool $register)
    {
        $this->value = $value;
        $this->register = $register;
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        return $this->value;
    }
}
