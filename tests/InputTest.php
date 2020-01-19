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

use Korowai\Lib\Ldif\Input;
use Korowai\Lib\Ldif\InputInterface;
use Korowai\Lib\Ldif\Util\IndexMap;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class InputTest extends TestCase
{
    public function test__implements__InputInterface()
    {
        $this->assertImplementsInterface(InputInterface::class, Input::class);
    }

    public function test__construct()
    {
        $im = $this->createMock(IndexMap::class);
        $input = new Input("source string", "the string", $im);

        $this->assertSame("source string", $input->getSourceString());
        $this->assertSame("the string", $input->getString());
        $this->assertSame($im, $input->getIndexMap());
        $this->assertSame('-', $input->getSourceFileName());
    }

    public function test__construct__withSourceFileName()
    {
        $im = $this->createMock(IndexMap::class);
        $input = new Input("source string", "the string", $im, 'foo.ldif');

        $this->assertSame("source string", $input->getSourceString());
        $this->assertSame("the string", $input->getString());
        $this->assertSame($im, $input->getIndexMap());
        $this->assertSame('foo.ldif', $input->getSourceFileName());
    }

    public function test__init()
    {
        $im1 = $this->createMock(IndexMap::class);
        $input = new Input("", "", $im1);

        $im2 = $this->createMock(IndexMap::class);
        $input->init("source string", "the string", $im2, 'foo.ldif');

        $this->assertSame("source string", $input->getSourceString());
        $this->assertSame("the string", $input->getString());
        $this->assertSame($im2, $input->getIndexMap());
        $this->assertSame('foo.ldif', $input->getSourceFileName());
    }

    public function test__toString()
    {
        $im = $this->createMock(IndexMap::class);
        $input = new Input("source string", "the string", $im, 'foo.ldif');

        $this->assertSame("the string", (string)$input);
    }

    public function test__getSourceOffset()
    {
        $im = $this->createMock(IndexMap::class);
        $im->expects($this->once())
           ->method('__invoke')
           ->with(12)
           ->willReturn(21);

        $input = new Input("", "", $im);
        $this->assertSame(21, $input->getSourceOffset(12));
    }

    public function sourceCharOffsetCases()
    {
        return [
            [
                //                01234 56789    0123
                new Input("# com\nline", "line", new IndexMap([[0,6]])),
                [
                //  l         i         n         e
                    [[0], 6], [[1], 7], [[2], 8], [[3], 9]
                ],
            ],

            [
                new Input("# com\nwóz", "wóz", new IndexMap([[0,6]])),
                [
                //  w         ó         z
                    [[0], 6], [[1], 7], [[3], 8]
                ],
            ],

            [
                new Input("zważy\n#com\ndrób", "zważy\ndrób", new IndexMap([[0,0], [7, 12]])),
                [
                //  z        w        a        ż        y        \n       d         r         ó         b
                    [[0],0], [[1],1], [[2],2], [[3],3], [[5],4], [[6],5], [[7],11], [[8],12], [[9],13], [[11],14]
                ],
            ],

            [
                new Input("zważy\n#tło\ndrób", "zważy\ndrób", new IndexMap([[0,0], [7, 13]])),
                [
                //  z        w        a        ż        y        \n       d         r         ó         b
                    [[0],0], [[1],1], [[2],2], [[3],3], [[5],4], [[6],5], [[7],11], [[8],12], [[9],13], [[11],14]
                ],
            ],
        ];
    }

    /**
     * @dataProvider sourceCharOffsetCases
     */
    public function test__getSourceCharOffset(Input $input, array $cases)
    {
        foreach ($cases as $case) {
            [$args, $expect] = $case;
            $this->assertSame($expect, $input->getSourceCharOffset(...$args));
        }
    }

    public function sourceLinesCases()
    {
        return [
            [
                new Input("", "", new IndexMap([])),
                [""],
                [[-PHP_INT_MAX,-1], [0,0]]
            ],

            [
                new Input("line 1", "line 1", new IndexMap([])),
                ["line 1"],
                [[-PHP_INT_MAX,-1], [0,0]]
            ],

            [
                //                000000 00001 1111111    000000 0001111
                //                012345 67890 1234567    123456 7890123
                new Input("line 1\n#com\nline 2", "line 1\nline 2", new IndexMap([[8,12]])),
                ["line 1", "#com", "line 2"],
                [[-PHP_INT_MAX,-1], [0,0], [7,1], [12,2]]
            ],

            [
                //                000 0000 0 0011
                //                012 3456 7 8901
                new Input("l 1\nl 2\r\nl 3", "l 1\nl 2\r\nl 3", new IndexMap([])),
                ["l 1", "l 2", "l 3"],
                [[-PHP_INT_MAX,-1], [0,0], [4,1], [9,2]]
            ],
        ];
    }

    /**
     * @dataProvider sourceLinesCases
     */
    public function test__sourceLines(Input $input, array $expLines, array $expLinesMap)
    {
        $this->assertSame($expLines, $input->getSourceLines());
        $this->assertSame(count($expLines), $input->getSourceLinesCount());
        $this->assertSame($expLinesMap, $input->getSourceLinesMap()->getArray());
        $this->assertSame(0, $input->getSourceLinesMap()->getIncrement());
    }

    public function sourceLineCases()
    {
        return [
            [
                new Input("", "", new IndexMap([])),
                [0 => ""]
            ],

            [
                new Input("l 1", " l 1", new IndexMap([])),
                [0 => "l 1"]
            ],

            [
                new Input("l 1\nl 2\r\nl 3", " l 1\nl 2\r\nl 3", new IndexMap([])),
                [0 => "l 1", 1 => "l 2", 2 => "l 3"]
            ],

            [
                //                000 00000 0 0111    000 000
                //                012 34567 8 9012    012 3456
                new Input("l 1\n#l 2\r\nl 3", "l 1\nl 3", new IndexMap([[4,10]])),
                [0 => "l 1", 1 => "#l 2", 2 => "l 3"]
            ],
        ];
    }

    /**
     * @dataProvider sourceLineCases
     */
    public function test__getSourceLine(Input $input, array $cases)
    {
        foreach ($cases as $i => $expect) {
            $this->assertSame($expect, $input->getSourceLine($i));
        }
    }

    public function sourceLineIndexCases()
    {
        return [
            [
                new Input("", "", new IndexMap([])),
                [0 => 0, 1 => 0]
            ],

            [
                //                012 3    012 3
                new Input("l 1\n", "l 1\n", new IndexMap([])),
                [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 1, 5 => 1]
            ],

            [
                //                000 00000 0 0111 1    000 0000 00
                //                012 34567 8 9012 3    012 3456 78
                new Input("l 1\n#l 2\r\nl 3\n", "l 1\nl 3\n", new IndexMap([[0,0], [4,10]])),
                [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 2, 5 => 2, 6 => 2, 7 => 2, 8 => 3, 9 => 3]
            ],
        ];
    }

    /**
     * @dataProvider sourceLineIndexCases
     */
    public function test__getSourceLineIndex(Input $input, array $cases)
    {
        foreach ($cases as $i => $j) {
            $this->assertSame($j, $input->getSourceLineIndex($i));
        }
    }

    public function sourceLineAndOffsetCases()
    {
        return [
            [
                new Input("", "", new IndexMap([])),
                [0 => [0,0], 1 => [0,1]],
            ],

            [
                //                012 3    012 3
                new Input("l 1\n", "l 1\n", new IndexMap([])),
                //    l           _           1           \n
                [0 => [0,0], 1 => [0,1], 2 => [0,2], 3 => [0,3], 4 => [1,0], 5 => [1,1]],
            ],

            [
                //                000 00000 0 0111 1    000 0000 00
                //                012 34567 8 9012 3    012 3456 78
                new Input("l 1\n#l 2\r\nl 3\n", "l 1\nl 3\n", new IndexMap([[0,0], [4,10]])),
                [
                    0 => [0,0], // l
                    1 => [0,1], // <space>
                    2 => [0,2], // 1
                    3 => [0,3], // \n
                    4 => [2,0], // l
                    5 => [2,1], // <space>
                    6 => [2,2], // 3
                    7 => [2,3], // \n
                    8 => [3,0], // EOF
                    9 => [3,1], // EOF
                ]
            ],

            [
                //                000 00000 1 1111 1    000 0000 01
                //                013 45689 0 1234 6    013 4567 90
                new Input("lód\n#łan\r\nryż\n", "lód\nryż\n", new IndexMap([[0,0], [5,12]])),
                [
                    0 => [0,0], // l
                    1 => [0,1], // ó
                    3 => [0,3], // d
                    4 => [0,4], // \n
                    5 => [2,0], // r
                    6 => [2,1], // y
                    7 => [2,2], // ż
                    9 => [2,4], // \n
                   10 => [3,0], // EOF
                   11 => [3,1], // EOF
                ]
            ],

        ];
    }

    /**
     * @dataProvider sourceLineAndOffsetCases
     */
    public function test__getSourceLineAndOffset(Input $input, array $cases)
    {
        foreach ($cases as $i => $expect) {
            [$expLine, $expOffset] = $expect;
            [$line, $offset] = $input->getSourceLineAndOffset($i);
            $this->assertSame($expLine, $line);
            $this->assertSame($expOffset, $offset);
        }
    }

    public function test__getSourceLineAndOffset__withEmptyMap()
    {
        $input = new class("", "", new IndexMap([])) extends Input {
            public function getSourceLinesMap() : IndexMap
            {
                return new IndexMap([], 0);
            }
        };

        $this->assertSame([-1,0], $input->getSourceLineAndOffset(-1));
        $this->assertSame([ 0,0], $input->getSourceLineAndOffset(0));
        $this->assertSame([ 1,0], $input->getSourceLineAndOffset(1));
    }

    public function sourceLineAndCharOffsetCases()
    {
        return [
            [
                new Input("", "", new IndexMap([])),
                [0 => [0,0], 1 => [0,0]],
            ],

            [
                //                012 3    012 3
                new Input("l 1\n", "l 1\n", new IndexMap([])),
                //    l           _           1           \n
                [0 => [0,0], 1 => [0,1], 2 => [0,2], 3 => [0,3], 4 => [1,0], 5 => [1,0]],
            ],

            [
                //                000 00000 0 0111 1    000 0000 00
                //                012 34567 8 9012 3    012 3456 78
                new Input("l 1\n#l 2\r\nl 3\n", "l 1\nl 3\n", new IndexMap([[0,0], [4,10]])),
                [
                    0 => [0,0], // l
                    1 => [0,1], // <space>
                    2 => [0,2], // 1
                    3 => [0,3], // \n
                    4 => [2,0], // l
                    5 => [2,1], // <space>
                    6 => [2,2], // 3
                    7 => [2,3], // \n
                    8 => [3,0], // EOF
                    9 => [3,0], // EOF
                ]
            ],

            [
                //                000 00000 1 1111 1    000 0000 01
                //                013 45689 0 1234 6    013 4567 90
                new Input("lód\n#łan\r\nryż\n", "lód\nryż\n", new IndexMap([[0,0], [5,12]])),
                [
                    0 => [0,0], // l
                    1 => [0,1], // ó
                    3 => [0,2], // d
                    4 => [0,3], // \n
                    5 => [2,0], // r
                    6 => [2,1], // y
                    7 => [2,2], // ż
                    9 => [2,3], // \n
                   10 => [3,0], // EOF
                   11 => [3,0], // EOF
                ]
            ],

        ];
    }

    /**
     * @dataProvider sourceLineAndCharOffsetCases
     */
    public function test__getSourceLineAndCharOffset(Input $input, array $cases)
    {
        foreach ($cases as $i => $expect) {
            [$expLine, $expOffset] = $expect;
            [$line, $offset] = $input->getSourceLineAndCharOffset($i);
            $this->assertSame($expLine, $line);
            $this->assertSame($expOffset, $offset);
        }
    }

    public function test__sourceFileName()
    {
        $im = new IndexMap([]);
        $input = new Input('', '', $im);
        $this->assertSame('-', $input->getSourceFileName());

        $input = new Input('', '', $im, 'foo.ldif');
        $this->assertSame('foo.ldif', $input->getSourceFileName());
        $input->setSourceFileName('bar.ldif');
        $this->assertSame('bar.ldif', $input->getSourceFileName());
    }
}

// vim: syntax=php sw=4 ts=4 et:
