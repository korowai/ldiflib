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

use Korowai\Lib\Ldif\Scan;
use Korowai\Lib\Ldif\CursorInterface;
use Korowai\Lib\Ldif\LocationInterface;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ScanTest extends TestCase
{
    protected function configureLocationMock(LocationInterface $location, array $case)
    {
        $location->expects($this->once())
                 ->method('getString')
                 ->with()
                 ->willReturn($case[1]);
        $location->expects($this->once())
                 ->method('getOffset')
                 ->with()
                 ->willReturn($case[3] ?? 0);
    }

    protected function configureCursorMock(CursorInterface $cursor, array $case, ?int $expMoveTo)
    {
        $this->configureLocationMock($cursor, $case);

        if ($expMoveTo !== null) {
            $cursor->expects($this->once())
                   ->method('moveTo')
                   ->with($expMoveTo);
        } else {
            $cursor->expects($this->never())
                   ->method('moveTo');
        }
    }

    protected function createLocationMock(array $case)
    {
        $location = $this->getMockBuilder(LocationInterface::class)
                         ->getMockForAbstractClass();
        $this->configureLocationMock($location, $case);
        return $location;
    }

    protected function createCursorMock(array $case, ?int $expMoveTo)
    {
        $cursor = $this->getMockBuilder(CursorInterface::class)
                       ->getMockForAbstractClass();
        $this->configureCursorMock($cursor, $case, $expMoveTo);
        return $cursor;
    }

    public static function matchAt__cases()
    {
        return [
            [['//', ''], ['']],
            [['/foo/', 'asdf asdf'], []],
            [['/(\w+)bar/', 'foo rabarbar baz'], ['rabarbar', 'rabar']],
            [['/(\w+)bar/', 'foo rabarbar baz', PREG_OFFSET_CAPTURE], [['rabarbar', 4], ['rabar', 4]]],
            [['/(\w+)bar/', 'foo rabarbar baz', 0, 6], ['barbar', 'bar']],
        ];
    }

    /**
     * @dataProvider matchAt__cases
     */
    public function test__matchAt(array $case, array $expected)
    {
        $location = $this->createLocationMock($case);
        $this->configureLocationMock($location, $case);

        $args = array_merge([$case[0], $location], count($case) > 2 ? [$case[2]] : []);
        $this->assertSame($expected, Scan::matchAt(...$args));
    }

    public static function matchAhead__cases()
    {
        return [
            [['//', ''], [['', 0]], 0],
            [['/foo/', 'asdf asdf'], []],
            [['/(\w+)bar/', 'foo rabarbar baz'], [['rabarbar', 4], ['rabar', 4]], 12],
            [['/(\w+)bar/', 'foo rabarbar baz', PREG_OFFSET_CAPTURE], [['rabarbar', 4], ['rabar', 4]], 12],
            [['/(\w+)bar/', 'foo rabarbar baz', 0, 6], [['barbar', 6], ['bar', 6]], 12],
        ];
    }

    /**
     * @dataProvider matchAhead__cases
     */
    public function test__matchAhead(array $case, array $expected, int $expMoveTo = null)
    {
        $cursor = $this->createCursorMock($case, $expMoveTo);
        $args = array_merge([$case[0], $cursor], count($case) > 2 ? [$case[2]] : []);
        $this->assertSame($expected, Scan::matchAhead(...$args));
    }

    /**
     * @dataProvider matchAt__cases
     */
    public function test__matchString(array $case, array $expected)
    {
        $this->assertSame($expected, Scan::matchString(...$case));
    }

    public static function matched__cases()
    {
        return [
            // #0
            [
                'key'       => 0,
                'matches'   => [],
                'expect'    => [
                    'result' => false,
                    'string' => null,
                    'offset' => -1,
                ]
            ],
            // #1
            [
                'key'       => 'foo',
                'matches'   => ['bar' => ['BAR', 4]],
                'expect'    => [
                    'result' => false,
                    'string' => null,
                    'offset' => -1,
                ]
            ],
            // #2
            [
                'key'       => 'foo',
                'matches'   => ['foo' => null],
                'expect'    => [
                    'result' => false,
                    'string' => null,
                    'offset' => -1,
                ]
            ],
            // #3
            [
                'key'       => 'foo',
                'matches'   => ['foo' => [null, 4]],
                'expect'    => [
                    'result' => false,
                    'string' => null,
                    'offset' => 4,
                ]
            ],
            // #4
            [
                'key'       => 'foo',
                'matches'   => ['foo' => ['FOO', -2]],
                'expect'    => [
                    'result' => false,
                    'string' => 'FOO',
                    'offset' => -2,
                ]
            ],
            // #5
            [
                'key'       => 'foo',
                'matches'   => ['foo' => ['FOO', 3]],
                'expect'    => [
                    'result' => true,
                    'string' => 'FOO',
                    'offset' => 3,
                ]
            ],
        ];
    }

    /**
     * @dataProvider matched__cases
     */
    public function test__matched($key, array $matches, array $expect)
    {
        $this->assertSame($expect['result'], Scan::matched($key, $matches, $string, $offset));
        $this->assertSame($expect['string'], $string);
        $this->assertSame($expect['offset'], $offset);
    }
}

// vim: syntax=php sw=4 ts=4 et:
