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

use Korowai\Lib\Ldif\Control;
use Korowai\Lib\Ldif\ControlInterface;
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ControlTest extends TestCase
{
    public function test__implmements__ControlInterface()
    {
        $this->assertImplementsInterface(ControlInterface::class, Control::class);
    }

    public function test__construct()
    {
        $value = $this->getMockBuilder(ValueInterface::class)
                      ->getMockForAbstractClass();

        $ctl = new Control('foo', true, $value);
        $this->assertSame('foo', $ctl->getOid());
        $this->assertSame(true, $ctl->getCriticality());
        $this->assertSame($value, $ctl->getValueObject());
    }
}

// vim: syntax=php sw=4 ts=4 et:
