<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Lib\Ldif;

use Korowai\Lib\Ldif\AttrVal;
use Korowai\Lib\Ldif\AttrValInterface;
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AttrValTest extends TestCase
{
    public function test__implmements__AttrValInterface()
    {
        $this->assertImplementsInterface(AttrValInterface::class, AttrVal::class);
    }

    public function test__construct()
    {
        $value = $this->getMockBuilder(ValueInterface::class)
                      ->getMockForAbstractClass();

        $attrVal = new AttrVal('foo', $value);
        $this->assertSame('foo', $attrVal->getAttribute());
        $this->assertSame($value, $attrVal->getValueObject());
    }
}

// vim: syntax=php sw=4 ts=4 et:
