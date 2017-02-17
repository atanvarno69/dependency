<?php
/**
 * Container class file.
 *
 * @package   Atan\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2017 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

namespace Atan\Dependency;

class EntryProxy
{
    private $id;
    
    public function __construct(string $id)
    {
        $this->id = $id;
    }
    
    public function __toString(): string
    {
        return $this->id;
    }
}
