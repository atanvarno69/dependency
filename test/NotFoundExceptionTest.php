<?php
/**
 * NotFoundExceptionTest class file
 * 
 * Copyright (C) 2016 atanvarno.com
 *
 * This file is part of Atan\Dependency.
 *
 * Atan\Dependency is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Atan\Dependency is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Atan\Dependency.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   Atan\Dependency
 * @author    atan <https://github.com/atanvarno69>
 * @copyright 2016 atanvarno.com
 * @license   http://opensource.org/licenses/GPL-3.0 GNU GPL v3
 */

namespace Atan\Dependency\Test;

/** PHPUnit use block */
use PHPUnit_Framework_TestCase as TestCase;

use Atan\Dependency\Exception\NotFoundException;

/**
 * PSR-11 use block
 *
 * @todo Change to `Psr` namespace when PSR-11 accepted
 */
use Interop\Container\Exception\NotFoundException as NotFoundExceptionInterface;

class NotFoundExceptionTest extends TestCase
{   
    public function testContainerExceptionImplementsInterface()
    {
        $exception = new NotFoundException();
        $this->assertInstanceOf(NotFoundExceptionInterface::class, $exception);
    }
}
