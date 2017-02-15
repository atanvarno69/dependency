<?php
/**
 * Definition class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

class Definition
{
    /**
     * @var string  $className  Name of defined class.
     * @var mixed[] $parameters Constructor parameters for defined class.
     * @var bool    $register   Whether an instance should be registered.
     */
    private $className, $parameters, $register;
    
    public function __construct(
        string $className,
        array $parameters = [],
        bool $register = true
    ) {
        $this->className = $className;
        $this->parameters = $parameters;
        $this->register = $register;
    }
    
    public function getClassName(): string
    {
        return $this->className;
    }
    
    public function getParameters(): array
    {
        return $this->parameters;
    }
    
    public function getRegister(): bool
    {
        return $this->register;
    }
}
