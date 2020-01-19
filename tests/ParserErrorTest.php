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

use Korowai\Lib\Ldif\ParserError;
use Korowai\Lib\Ldif\ParserErrorInterface;
use Korowai\Lib\Ldif\Traits\DecoratesSourceLocationInterface;
use Korowai\Lib\Ldif\SourceLocationInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ParserErrorTest extends TestCase
{
    public function test__implements__ParserErrorInterface()
    {
        $this->assertImplementsInterface(ParserErrorInterface::class, ParserError::class);
    }

    public function test__implements__Throwable()
    {
        $this->assertImplementsInterface(\Throwable::class, ParserError::class);
    }

    public function test__extends__Exception()
    {
        $this->assertExtendsClass(\Exception::class, ParserError::class);
    }

    public function test__uses__DecoratesSourceLocationInterface()
    {
        $this->assertUsesTrait(DecoratesSourceLocationInterface::class, ParserError::class);
    }

    public function test__construct()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $error = new ParserError($location, 'foo');
        $this->assertSame($location, $error->getSourceLocation());
        $this->assertSame('foo', $error->getMessage());
        $this->assertSame(0, $error->getCode());
        $this->assertNull($error->getPrevious());

        $previous = new \RuntimeException();
        $error = new ParserError($location, 'foo', 123, $previous);
        $this->assertSame($location, $error->getSourceLocation());
        $this->assertSame('foo', $error->getMessage());
        $this->assertSame(123, $error->getCode());
        $this->assertSame($previous, $error->getPrevious());
    }

    public function test__getSourceLocationString()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->once())
                 ->method('getSourceLineAndCharOffset')
                 ->with()
                 ->willReturn([2,9]);
        $location->expects($this->once())
                 ->method('getSourceFileName')
                 ->with()
                 ->willReturn('foo.ldif');

        $error = new ParserError($location, '');

        $this->assertSame('foo.ldif:3:10', $error->getSourceLocationString());
    }

    public function test__getSourceLocationString__withLineAndChar()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->never())
                 ->method('getSourceLineAndCharOffset');
        $location->expects($this->once())
                 ->method('getSourceFileName')
                 ->with()
                 ->willReturn('foo.ldif');

        $error = new ParserError($location, 'invalid syntax');

        $this->assertSame('foo.ldif:2:5', $error->getSourceLocationString([1,4]));
    }

    public function test__getSourceLocationIndicator()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->once())
                 ->method('getSourceLineAndCharOffset')
                 ->with()
                 ->willReturn([0,3]);

        $error = new ParserError($location, '');

        //                 0123
        $this->assertSame('   ^', $error->getSourceLocationIndicator());
    }

    public function test__getSourceLocationIndicator__withLineAndChar()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->never())
                 ->method('getSourceLineAndCharOffset');

        $error = new ParserError($location, '');

        //                 0123
        $this->assertSame('   ^', $error->getSourceLocationIndicator([0,3]));
    }

    public function test__getMultilineMessageLines()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->once())
                 ->method('getSourceFileName')
                 ->with()
                 ->willReturn('foo.ldif');
        $location->expects($this->once())
                 ->method('getSourceLineAndCharOffset')
                 ->with()
                 ->willReturn([0,4]);
        $location->expects($this->once())
                 ->method('getSourceLine')
                 ->with(0)
                 ->willReturn('dn: %*&@@');

        $error = new ParserError($location, 'invalid DN syntax');

        $lines = $error->getMultilineMessageLines();

        $this->assertCount(3, $lines);
        $this->assertSame('foo.ldif:1:5:invalid DN syntax', $lines[0]);
        $this->assertSame('foo.ldif:1:5:dn: %*&@@', $lines[1]);
        $this->assertSame('foo.ldif:1:5:    ^', $lines[2]);
    }

    public function test__getMultilineMessage()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->once())
                 ->method('getSourceFileName')
                 ->with()
                 ->willReturn('foo.ldif');
        $location->expects($this->once())
                 ->method('getSourceLineAndCharOffset')
                 ->with()
                 ->willReturn([0,4]);
        $location->expects($this->once())
                 ->method('getSourceLine')
                 ->with(0)
                 ->willReturn('dn: %*&@@');

        $error = new ParserError($location, 'invalid DN syntax');

        $expected = "foo.ldif:1:5:invalid DN syntax\n".
                    "foo.ldif:1:5:dn: %*&@@\n".
                    "foo.ldif:1:5:    ^";

        $this->assertSame($expected, $error->getMultilineMessage());
    }

    public function test__toString()
    {
        $location = $this->getMockBuilder(SourceLocationInterface::class)
                         ->getMockForAbstractClass();
        $location->expects($this->once())
                 ->method('getSourceFileName')
                 ->with()
                 ->willReturn('foo.ldif');
        $location->expects($this->once())
                 ->method('getSourceLineAndCharOffset')
                 ->with()
                 ->willReturn([0,4]);

        $error = new ParserError($location, 'invalid DN syntax');

        $this->assertSame('foo.ldif:1:5:invalid DN syntax', (string)$error);
    }
}

// vim: syntax=php sw=4 ts=4 et:
