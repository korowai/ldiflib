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

use Korowai\Testing\Ldiflib\TestCase;
use Korowai\Lib\Ldif\Util\IndexMap;
use Korowai\Lib\Ldif\Util\IndexMapArrayCombineAlgorithm;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class IndexMapTest extends TestCase
{
    public function arrayFromPieces()
    {
        return [
            [ [],                                                       [] ],
            [ [['a piece', 0]],                                         [[0,0]] ],
            [ [['first piece', 0], ['second piece', 15]],               [[0,0], [11,15]] ],
            [ [['first piece', 4], ['second piece', 19]],               [[0,4], [11,19]] ],
        ];
    }

    /**
     * @dataProvider arrayFromPieces
     */
    public function test__arrayFromPieces($pieces, $expect)
    {
        $array = IndexMap::arrayFromPieces($pieces);
        $this->assertSame($expect, $array);
    }

    public function arrayIndexMapCases()
    {
        // For given index map array and increment (optional), define how
        // particular offsets are expected to be mapped.
        return [
            [
                [[[0,0]]], [
                    -2 => [-2, 0],
                     0 => [ 0, 0],
                     1 => [ 1, 0],
                     2 => [ 2, 0],
                    -1 => [-1, 0],
                ]
            ],
            [
                [[[0,0], [4,6], [8,12]]], [
                    -2 => [-2, 0],
                     0 => [ 0, 0],
                     1 => [ 1, 0],
                     2 => [ 2, 0],
                     3 => [ 3, 0],
                     4 => [ 6, 1],
                     5 => [ 7, 1],
                     6 => [ 8, 1],
                     7 => [ 9, 1],
                     8 => [12, 2],
                     9 => [13, 2],
                    10 => [14, 2],
                    -1 => [-1, 0],
                ]
            ],
            [
                [[[0,0], [4,6], [8,12]], 0], [
                    -2 => [ 0, 0],
                     0 => [ 0, 0],
                     1 => [ 0, 0],
                     2 => [ 0, 0],
                     3 => [ 0, 0],
                     4 => [ 6, 1],
                     5 => [ 6, 1],
                     6 => [ 6, 1],
                     7 => [ 6, 1],
                     8 => [12, 2],
                     9 => [12, 2],
                    10 => [12, 2],
                    -1 => [ 0, 0],
                ]
            ],
            [
                [[[4,6], [8,12]]], [
                    -2 => [ 0, 0],
                     0 => [ 2, 0],
                     1 => [ 3, 0],
                     2 => [ 4, 0],
                     3 => [ 5, 0],
                     4 => [ 6, 0],
                     5 => [ 7, 0],
                     6 => [ 8, 0],
                     7 => [ 9, 0],
                     8 => [12, 1],
                     9 => [13, 1],
                    10 => [14, 1],
                    -1 => [ 1, 0],
                ]
            ],
            [
                [[]], [
                    -1 => [-1, null],
                     0 => [ 0, null],
                     1 => [ 1, null],
                ]
            ]
        ];
    }

    /**
     * @dataProvider arrayIndexMapCases
     */
    public function test__arrayApply(array $args, array $cases)
    {
        $im = $args[0];
        $inc = $args[1] ?? 1;
        foreach ($cases as $i => $case) {
            $expect = $case[0];
            $expectIndex = $case[1];
            $this->assertSame($expect, IndexMap::arrayApply($im, $i, $inc, $index));
            $this->assertSame($expectIndex, $index);
        }
    }

    public function arraySearchCases()
    {
        return [
            [
                [[0,0]], [
                     0 => 0,
                     1 => 0,
                    41 => 0
                ]
            ],
            [
                [[0,0], [3,10], [5,19]], [
                     0 => 0,
                     1 => 0,
                     2 => 0,
                     3 => 1,
                     4 => 1,
                     5 => 2,
                     6 => 2,
                    41 => 2
                ]
            ],
        ];
    }

    /**
     * @dataProvider arraySearchCases
     */
    public function test__arraySearch($im, $cases)
    {
        foreach ($cases as $i => $j) {
            $this->assertSame($j, IndexMap::arraySearch($im, $i));
        }
    }

    public function arraySearchFailingCases()
    {
        return [
            [ [], 0 ],
            [ [], 1 ],
            [ [[0,0]], -1 ],
            [ [[1,12]],  0 ],
        ];
    }

    /**
     * @dataProvider arraySearchFailingCases
     */
    public function test__arraySearch__exception($im, $i)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('internal error: arraySearch() failed');

        IndexMap::arraySearch($im, $i);
    }

    /**
     * @dataProvider arrayFromPieces
     */
    public function test__createFromPieces($pieces, $expect)
    {
        $im = IndexMap::createFromPieces($pieces);
        $this->assertInstanceOf(IndexMap::class, $im);
        $this->assertSame($expect, $im->getArray());
        $this->assertSame(1, $im->getIncrement());
    }

    public function test__construct()
    {
        $im = new IndexMap([[0,1]]);
        $this->assertSame([[0,1]], $im->getArray());
        $this->assertSame(1, $im->getIncrement());
    }

    public function test__construct__withIncrement()
    {
        $im = new IndexMap([[0,1]], 0);
        $this->assertSame([[0,1]], $im->getArray());
        $this->assertEquals(0, $im->getIncrement());
    }

    public function test__arrayCombineAlgorithm()
    {
        $im = new IndexMap([]);
        $alg1 = $im->getArrayCombineAlgorithm();
        $this->assertInstanceOf(IndexMapArrayCombineAlgorithm::class, $alg1);
        $alg2 = new IndexMapArrayCombineAlgorithm;
        $this->assertSame($im, $im->setArrayCombineAlgorithm($alg2));
        $this->assertSame($alg2, $im->getArrayCombineAlgorithm());

        $this->assertSame($im, $im->setArrayCombineAlgorithm(null));
        $alg3 = $im->getArrayCombineAlgorithm();
        $this->assertInstanceOf(IndexMapArrayCombineAlgorithm::class, $alg3);
        $this->assertNotSame($alg2, $alg3);
    }

    public function indexMapCases()
    {
        return array_map(function (array $item) {
            return [new IndexMap(...($item[0])), $item[1]];
        }, $this->arrayIndexMapCases());
    }

    /**
     * @dataProvider indexMapCases
     */
    public function test__apply(IndexMap $im, array $cases)
    {
        foreach ($cases as $i => $case) {
            $expect = $case[0];
            $expectIndex = $case[1];
            $this->assertSame($expect, $im->apply($i, $index));
            $this->assertSame($expectIndex, $index);
        }
    }


    public function test__invoke()
    {
        // apply() is already tested, so we only check that it's properly used
        $im = $this->getMockBuilder(IndexMap::class)
                   ->setMethods(['apply'])
                   ->disableOriginalConstructor()
                   ->getMock();
        $im->expects($this->once())
           ->method('apply')
           ->with(3)
           ->willReturnCallback(function (int $i, int &$index = null) {
               $index = 123;
               return 7;
           });

        $this->assertEquals(7, $im(3, $index));
        $this->assertEquals(123, $index);
    }

    public function test__combineWithArray()
    {
        // The IndexMapArrayCombineAlgorithm is already tested, so only check
        // that it's properly used.
        $combine = $this->createMock(IndexMapArrayCombineAlgorithm::class);
        $combine->expects($this->once())
                ->method('__invoke')
                ->with(['A'], ['B'])
                ->willReturn(['A', 'B']);

        $im = new IndexMap(['A']);
        $im->setArrayCombineAlgorithm($combine);


        $this->assertSame($im, $im->combineWithArray(['B']));
        $this->assertSame(['A', 'B'], $im->getArray());
    }

    public function test__combineWith()
    {
        // combineWithArray() is already tested, so we only check that
        // combineWith() calls the combineWithArray() correctly.
        $im = $this->getMockBuilder(IndexMap::class)
                   ->setMethods(['combineWithArray'])
                   ->disableOriginalConstructor()
                   ->getMock();
        $im->expects($this->once())
           ->method('combineWithArray')
           ->with([[10,20], [30,40]])
           ->will($this->returnSelf());

        $jm = new IndexMap([[10,20], [30,40]]);

        $this->assertSame($im, $im->combineWith($jm));
    }
}

// vim: syntax=php sw=4 ts=4 et:
