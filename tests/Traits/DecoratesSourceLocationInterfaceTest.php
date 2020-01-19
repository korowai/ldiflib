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

use Korowai\Lib\Ldif\Traits\DecoratesSourceLocationInterface;
use Korowai\Lib\Ldif\Traits\ExposesSourceLocationInterface;
use Korowai\Lib\Ldif\SourceLocationInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class DecoratesSourceLocationInterfaceTest extends TestCase
{
    public function getTestObject()
    {
        return new class {
            use DecoratesSourceLocationInterface;
        };
    }

    public function test__uses__ExposesSourceLocationInterface()
    {
        $this->assertUsesTrait(ExposesSourceLocationInterface::class, DecoratesSourceLocationInterface::class);
    }

    public function test__sourceLocation()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();

        $obj = $this->getTestObject();
        $this->assertNull($obj->getSourceLocation());

        $this->assertSame($obj, $obj->setSourceLocation($location));
        $this->assertSame($location, $obj->getSourceLocation());
    }
}

// vim: syntax=php sw=4 ts=4 et:
