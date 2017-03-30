<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atanvarno\Dependency\Definition;

class ValueDefinition extends AbstractDefinition
{
    private $value;
    
    public function __construct($value, bool $register)
    {
        $this->value = $value;
        parent::__construct($register);
    }
    
    protected function factoryMethod(ContainerInterface $container)
    {
        return $this->value;
    }
}
