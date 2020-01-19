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

use Korowai\Lib\Ldif\Cursor;
use Korowai\Lib\Ldif\CursorInterface;
use Korowai\Lib\Ldif\Location;
use Korowai\Lib\Ldif\Input;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class CursorTest extends TestCase
{
    public function test__implements__CursorInterface()
    {
        $this->assertImplementsInterface(CursorInterface::class, Cursor::class);
    }

    public function test__extends__Location()
    {
        $this->assertExtendsClass(Location::class, Cursor::class);
    }

    public function test__getClonedLocation()
    {
        $input = $this->createMock(Input::class);

        $cursor = new Cursor($input, 123);

        $clone1 = $cursor->getClonedLocation();
        $this->assertInstanceOf(Location::class, $clone1);
        $this->assertNotInstanceOf(Cursor::class, $clone1);
        $this->assertSame($input, $clone1->getInput());
        $this->assertSame(123, $clone1->getOffset());

        $clone2 = $cursor->getClonedLocation(null);
        $this->assertInstanceOf(Location::class, $clone2);
        $this->assertNotInstanceOf(Cursor::class, $clone2);
        $this->assertSame($input, $clone2->getInput());
        $this->assertSame(123, $clone2->getOffset());

        $clone3 = $cursor->getClonedLocation(321);
        $this->assertInstanceOf(Location::class, $clone3);
        $this->assertNotInstanceOf(Cursor::class, $clone3);
        $this->assertSame($input, $clone3->getInput());
        $this->assertSame(321, $clone3->getOffset());
    }

    public function test__moveBy()
    {
        $input = $this->createMock(Input::class);
        $cursor = new Cursor($input, 0);

        $this->assertSame(0, $cursor->getOffset());

        $this->assertSame($cursor, $cursor->moveBy(2));
        $this->assertSame(2, $cursor->getOffset());

        $this->assertSame($cursor, $cursor->moveBy(3));
        $this->assertSame(5, $cursor->getOffset());

        $this->assertSame($cursor, $cursor->moveBy(-2));
        $this->assertSame(3, $cursor->getOffset());
    }

    public function test__moveTo()
    {
        $input = $this->createMock(Input::class);
        $cursor = new Cursor($input, 0);

        $this->assertSame(0, $cursor->getOffset());

        $this->assertSame($cursor, $cursor->moveTo(2));
        $this->assertSame(2, $cursor->getOffset());

        $this->assertSame($cursor, $cursor->moveTo(3));
        $this->assertSame(3, $cursor->getOffset());
    }
}

// vim: syntax=php sw=4 ts=4 et:
