<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Lib\Ldif\Traits;

use Korowai\Lib\Ldif\Traits\DecoratesLocationInterface;
use Korowai\Lib\Ldif\Traits\ExposesLocationInterface;
use Korowai\Lib\Ldif\LocationInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class DecoratesLocationInterfaceTest extends TestCase
{
    public function getTestObject()
    {
        return new class {
            use DecoratesLocationInterface;
        };
    }

    public function test__uses__ExposesLocationInterface()
    {
        $this->assertUsesTrait(ExposesLocationInterface::class, DecoratesLocationInterface::class);
    }

    public function test__location()
    {
        $location = $this->getMockBuilder(LocationInterface::class)
                         ->getMockForAbstractClass();

        $obj = $this->getTestObject();
        $this->assertNull($obj->getLocation());

        $this->assertSame($obj, $obj->setLocation($location));
        $this->assertSame($location, $obj->getLocation());
    }
}

// vim: syntax=php sw=4 ts=4 et:
