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

use Korowai\Lib\Ldif\ParserState;
use Korowai\Lib\Ldif\ParserStateInterface;
use Korowai\Lib\Ldif\RecordInterface;
use Korowai\Lib\Ldif\CursorInterface;
use Korowai\Lib\Ldif\LocationInterface;
use Korowai\Lib\Ldif\ParserError;
use Korowai\Lib\Ldif\VersionSpecInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ParserStateTest extends TestCase
{
    public function test__implements__ParserStateInterface()
    {
        $this->assertImplementsInterface(ParserStateInterface::class, ParserState::class);
    }

    public function constructCases()
    {
        $cursor = $this->getMockBuilder(CursorInterface::class)->getMock();
        $version = $this->getMockBuilder(VersionSpecInterface::class)->getMock();
        return [
            [$cursor],
            [$cursor, []],
            [$cursor, null],
            [$cursor, ['E']],
            [$cursor, ['E'], []],
            [$cursor, ['E'], null],
            [$cursor, ['E'], ['R']],
            [$cursor, ['E'], ['R'], null],
            [$cursor, ['E'], ['R'], $version],
        ];
    }

    /**
     * @dataProvider constructCases
     */
    public function test__construct(...$args)
    {
        $state = new ParserState(...$args);

        $this->assertSame($args[0], $state->getCursor());

        if (count($args[1] ?? []) === 0) {
            $this->assertSame([], $state->getErrors());
            $this->assertTrue($state->isOk());
        } else {
            $this->assertSame($args[1], $state->getErrors());
            $this->assertFalse($state->isOk());
        }

        if (count($args[2] ?? []) === 0) {
            $this->assertSame([], $state->getRecords());
        } else {
            $this->assertSame($args[2], $state->getRecords());
        }

        if (count($args) >= 4) {
            $this->assertSame($args[3], $state->getVersionSpec());
        } else {
            $this->assertNull($state->getVersionSpec());
        }
    }

    protected function createParserState(...$args)
    {
        $cursor = $args[0] ?? $this->getMockBuilder(CursorInterface::class)->getMock();
        return new ParserState($cursor, array_slice($args, 1));
    }

    public function test__cursor()
    {
        $state = $this->createParserState();
        $cursor = $this->getMockBuilder(CursorInterface::class)->getMock();

        $this->assertSame($state, $state->setCursor($cursor));
        $this->assertSame($cursor, $state->getCursor());
    }

    public function test__errors()
    {
        $state = $this->createParserState();

        $this->assertSame([], $state->getErrors());
        $this->assertTrue($state->isOk());

        $this->assertSame($state, $state->setErrors(['E']));
        $this->assertSame(['E'], $state->getErrors());
        $this->assertFalse($state->isOk());
    }

    public function test__records()
    {
        $state = $this->createParserState();

        $this->assertSame([], $state->getRecords());

        $this->assertSame($state, $state->setRecords(['R']));
        $this->assertSame(['R'], $state->getRecords());
    }

    public function test__appendError()
    {
        $state = $this->createParserState();
        // Due to a bug in phpunit we can't mock interfaces that extend \Throwable.
        $error = $this->createMock(ParserError::class);

        $this->assertSame([], $state->getErrors());
        $this->assertSame($state, $state->appendError($error));
        $this->assertSame([$error], $state->getErrors());
    }

    public function test__versionSpec()
    {
        $state = $this->createParserState();
        $version = $this->getMockBuilder(VersionSpecInterface::class)->getMock();

        $this->assertNull($state->getVersionSpec());
        $this->assertSame($state, $state->setVersionSpec($version));
        $this->assertSame($version, $state->getVersionSpec());
    }

    public static function errorHere__cases()
    {
        return [
            ['error message'],
            ['error message', []],
            ['error message', [2]],
            ['error message', [2, new \Exception('previous exception')]]
        ];
    }

    /**
     * @dataProvider errorHere__cases
     */
    public function test__errorHere(string $message, ...$tail)
    {
        $state = $this->createParserState();

        $location = $this->getMockBuilder(LocationInterface::class)->getMock();

        $cursor = $state->getCursor();
        $cursor->expects($this->once())
               ->method('getClonedLocation')
               ->with()
               ->willReturn($location);
        $cursor->expects($this->never())
               ->method('moveTo');
        $cursor->expects($this->never())
               ->method('moveBy');

        $this->assertSame([], $state->getErrors());
        $this->assertSame($state, $state->errorHere($message, ...$tail));

        $errors = $state->getErrors();
        $this->assertCount(1, $errors);

        $error = $errors[0];
        $this->assertInstanceOf(ParserError::class, $error);
        $this->assertSame($message, $error->getMessage());
        $this->assertSame($location, $error->getSourceLocation());

        if (($code = $tail[0][0] ?? null) !== null) {
            $this->assertSame($code, $error->getCode());
        }
        if (($previous = $tail[0][1] ?? null) !== null) {
            $this->assertSame($previous, $error->getPrevious());
        }
    }

    public static function errorAt__cases(string $message, ...$tail)
    {
        return [
            [123, 'error message'],
            [123, 'error message', []],
            [123, 'error message', [2]],
            [123, 'error message', [2, new \Exception('previous exception')]]
        ];
    }

    /**
     * @dataProvider errorAt__cases
     */
    public function test__errorAt(int $offset, string $message, ...$tail)
    {
        $state = $this->createParserState();

        $location = $this->getMockBuilder(LocationInterface::class)->getMock();

        $cursor = $state->getCursor();
        $cursor->expects($this->once())
               ->method('getClonedLocation')
               ->with()
               ->willReturn($location);
        $cursor->expects($this->never())
               ->method('moveTo');
        $cursor->expects($this->never())
               ->method('moveBy');

        $this->assertSame([], $state->getErrors());
        $this->assertSame($state, $state->errorAt($offset, $message, ...$tail));

        $errors = $state->getErrors();
        $this->assertCount(1, $errors);

        $error = $errors[0];
        $this->assertInstanceOf(ParserError::class, $error);
        $this->assertSame($message, $error->getMessage());
        $this->assertSame($location, $error->getSourceLocation());

        if (($code = $tail[0][0] ?? null) !== null) {
            $this->assertSame($code, $error->getCode());
        }
        if (($previous = $tail[0][1] ?? null) !== null) {
            $this->assertSame($previous, $error->getPrevious());
        }
    }

    public function test__appendRecord()
    {
        $state = $this->createParserState();
        $record = $this->getMockBuilder(RecordInterface::class)->getMock();

        $this->assertSame([], $state->getRecords());
        $this->assertSame($state, $state->appendRecord($record));
        $this->assertSame([$record], $state->getRecords());
    }
}

// vim: syntax=php sw=4 ts=4 et:
