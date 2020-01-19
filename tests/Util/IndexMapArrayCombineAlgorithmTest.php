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
use Korowai\Lib\Ldif\Util\IndexMapArrayCombineAlgorithm as Algorithm;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class IndexMapArrayCombineAlgorithmTest extends TestCase
{
    public function arrayCombineProvider()
    {
        return [
            // $expected                            $old                                $new
            [ [],                                   [],                                 []                      ],
            [ [[0,0]],                              [[0,0]],                            []                      ],
            [ [[0,0]],                              [],                                 [[0,0]]                 ],
            [ [[2,4], [3,6], [4,9]],                [[2,4], [5,9]],                     [[3,4]]                 ],
            [ [[0,0], [5,7], [12,16]],              [],                                 [[0,0], [5,7], [12,16]] ],
            [ [[0,0], [5,7], [12,16]],              [[0,0]],                            [[0,0], [5,7], [12,16]] ],
            [ [[0,0], [5,7], [12,16], [15,20]],     [[0,0], [19,20]],                   [[0,0], [5,7], [12,16]] ],
            [ [[0,0]],                              [[0,0]],                            [[0,0]]                 ],
            [ [[0,0], [4,8], [6,15]],               [[0,0], [10,15]],                   [[0,0], [4,8]]          ],
            [ [[0,0], [4,23]],                      [[0,0], [10,15]],                   [[0,0], [4,18]]         ],
            [ [[0,0], [4,8], [9,18]],               [[0,0], [4,8]],                     [[0,0], [9,14]]         ],
            [ [[0,0], [2,4], [3,10], [4,11]],       [[0,0], [5,10]],                    [[0,0], [2,4], [4,6]]   ],
            [ [[0,0], [2,4], [3,11]],               [[0,0], [5,10]],                    [[0,0], [2,4], [3,6]]   ],
            [ [[0,12], [5,19], [12,28], [30, 48]],  [[0,0], [17,19], [24,28], [42,48]], [[0,12]]                ],
            [ [[2,10]],                             [[3,5], [5,9]],                     [[2,6]]                 ],
        ];
    }

    /**
     * @dataProvider arrayCombineProvider
     */
    public function test__invoke(array $expected, array $old, array $new)
    {
        $combine = new Algorithm;
        $this->assertSame($expected, $combine($old, $new));
    }

    public function internalErrorCases()
    {
        return [
            [
                new class extends Algorithm {
                    public function __invoke(array $old, array $new) : array
                    {
                        $this->reset($old, $new);
                        $this->stepBefore();
                        return [];
                    }
                },
                [[0,0]], [],
            ],
            [
                new class extends Algorithm {
                    public function __invoke(array $old, array $new) : array
                    {
                        $this->reset($old, $new);
                        $this->stepAfter();
                        return [];
                    }
                },
                [], [[0,0]]
            ],
            [
                new class extends Algorithm {
                    public function __invoke(array $old, array $new) : array
                    {
                        $this->reset($old, $new);
                        $this->stepEnclosing();
                        return [];
                    }
                },
                [[0,0]], []
            ],
            [
                new class extends Algorithm {
                    public function __invoke(array $old, array $new) : array
                    {
                        $this->reset($old, $new);
                        $this->stepEnclosing();
                        return [];
                    }
                },
                [], [[0,0]]
            ],
        ];
    }

    /**
     * @dataProvider internalErrorCases
     */
    public function test__invoke__internalError($combine, $old, $new)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('internal error');

        $combine($old, $new);
    }
}

// vim: syntax=php sw=4 ts=4 et:
