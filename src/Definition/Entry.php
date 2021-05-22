<?php
/**
 * @package   Atanvarno\Dependency
 * @author    atanvarno69 <https://github.com/atanvarno69>
 * @copyright 2021 atanvarno.com
 * @license   https://opensource.org/licenses/MIT The MIT License
 */

declare(strict_types = 1);

namespace Atanvarno\Dependency\Definition;

/**
 * Represents a container entry in definitions.
 *
 * Returned by the helper function `entry()`.
 */
class Entry
{
    /**
     * Build an `Entry` instance from a string identifier.
     */
    public function __construct(private string $id)
    {}
    
    /**
     * Get the entry identifier.
     */
    public function __toString(): string
    {
        return $this->id;
    }
}
