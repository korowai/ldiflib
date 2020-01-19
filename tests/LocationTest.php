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

use Korowai\Lib\Ldif\Location;
use Korowai\Lib\Ldif\LocationInterface;
use Korowai\Lib\Ldif\InputInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class LocationTest extends TestCase
{
    public function test__implements__LocationInterface()
    {
        $this->assertImplementsInterface(LocationInterface::class, Location::class);
    }

    public function test__construct()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $location = new Location($input, 12);

        $this->assertSame($input, $location->getInput());
        $this->assertSame(12, $location->getOffset());
    }

    public function test__isValid()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMock();

        $input->expects($this->any())
              ->method('getString')
              ->with()
              ->willReturn('FOO');

        $location = new Location($input, -1);
        $this->assertFalse($location->isValid());

        $location = new Location($input, 0);
        $this->assertTrue($location->isValid());

        $location = new Location($input, 1);
        $this->assertTrue($location->isValid());

        $location = new Location($input, 2);
        $this->assertTrue($location->isValid());

        $location = new Location($input, 3);
        $this->assertFalse($location->isValid());
    }

    public function test__getClonedLocation()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();

        $location = new Location($input, 123);

        $clone1 = $location->getClonedLocation();
        $this->assertInstanceOf(Location::class, $clone1);
        $this->assertNotSame($location, $clone1);
        $this->assertSame($input, $clone1->getInput());
        $this->assertSame(123, $clone1->getOffset());

        $clone2 = $location->getClonedLocation(null);
        $this->assertInstanceOf(Location::class, $clone2);
        $this->assertNotSame($location, $clone2);
        $this->assertSame($input, $clone2->getInput());
        $this->assertSame(123, $clone2->getOffset());

        $this->assertNotSame($clone1, $clone2);

        $clone3 = $location->getClonedLocation(321);
        $this->assertInstanceOf(Location::class, $clone3);
        $this->assertNotSame($location, $clone3);
        $this->assertSame($input, $clone3->getInput());
        $this->assertSame(321, $clone3->getOffset());
    }

    public function test__getString()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getString')
              ->with()
              ->willReturn('A');

        $location = new Location($input, 0);
        $this->assertSame('A', $location->getString());
    }

    public function charOffsetCases()
    {
        return [
            [
                "",
                [
                    0 => 0,
                    1 => 0,
                ]
            ],

            [
            //   012467
                "dałże",
                [
                    0 => 0, // d
                    1 => 1, // a
                    2 => 2, // ł
                    4 => 3, // ż
                    6 => 4, // e
                    7 => 5, // EOF
                ]
            ]
        ];
    }

    /**
     * @dataProvider charOffsetCases
     */
    public function test__getCharOffset(string $string, array $cases)
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->any())
              ->method('getString')
              ->with()
              ->willReturn($string);

        foreach ($cases as $i => $j) {
            $location = new Location($input, $i);
            $this->assertSame($j, $location->getCharOffset());
        }
    }

    public function test__getSourceFileName()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceFileName')
              ->with()
              ->willReturn('foo.ldif');

        $location = new Location($input, 0);
        $this->assertSame('foo.ldif', $location->getSourceFileName());
    }

    public function test__getSourceString()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceString')
              ->with()
              ->willReturn('A');

        $location = new Location($input, 0);
        $this->assertSame('A', $location->getSourceString());
    }

    public function test__getSourceOffset()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceOffset')
              ->with(2)
              ->willReturn(4);

        $location = new Location($input, 2);
        $this->assertSame(4, $location->getSourceOffset());
    }

    public function test__getSourceCharOffset()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceCharOffset')
              ->with(4, 'U')
              ->willReturn(2);

        $location = new Location($input, 4);
        $this->assertSame(2, $location->getSourceCharOffset('U'));
    }

    public function test__getSourceLineIndex()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceLineIndex')
              ->with(4)
              ->willReturn(1);

        $location = new Location($input, 4);
        $this->assertSame(1, $location->getSourceLineIndex());
    }

    public function test__getSourceLine()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceLineIndex')
              ->with(4)
              ->willReturn(1);
        $input->expects($this->once())
              ->method('getSourceLine')
              ->with(1)
              ->willReturn('A');

        $location = new Location($input, 4);
        $this->assertSame('A', $location->getSourceLine());
    }

    public function test__getSourceLineAndOffset()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceLineAndOffset')
              ->with(4)
              ->willReturn(['A',1]);

        $location = new Location($input, 4);
        $this->assertSame(['A',1], $location->getSourceLineAndOffset());
    }

    public function test__getSourceLineAndCharOffset()
    {
        $input = $this->getMockBuilder(InputInterface::class)
                      ->getMockForAbstractClass();
        $input->expects($this->once())
              ->method('getSourceLineAndCharOffset')
              ->with(4, 'U')
              ->willReturn(['A',1]);

        $location = new Location($input, 4);
        $this->assertSame(['A',1], $location->getSourceLineAndCharOffset('U'));
    }
}

// vim: syntax=php sw=4 ts=4 et:
