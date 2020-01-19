<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Lib\Ldif\Rules;

use Korowai\Lib\Ldif\Rules\SepRule;
use Korowai\Lib\Ldif\Rules\AbstractRfcRule;
use Korowai\Lib\Rfc\Rfc2849;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class SepRuleTest extends TestCase
{
    public function test__extendsAbstractRfcRule()
    {
        $this->assertExtendsClass(AbstractRfcRule::class, SepRule::class);
    }

    public static function construct__cases()
    {
        return [
            'default' => [
                'args'   => [],
                'expect' => [],
            ],
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = new SepRule(...$args);
        $expect = array_merge([
            'rfcRule' => self::hasPropertiesIdenticalTo([
                'ruleSetClass' => Rfc2849::class,
                'name' => 'SEP',
            ])
        ], $expect);
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    public static function dnMatch__cases()
    {
        return UtilTest::dnMatch__cases();
    }

    //
    // parseMatched()
    //
    public static function parseMatched__cases()
    {
        return [
            [
                'source'    => ["\n", 1],
                'matches'   => [["\n", 0]],
                'expect'    => [
                    'result' => true,
                    'value' => "\n",
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 1,
                            'sourceOffset' => 1,
                            'sourceCharOffset' => 1
                        ]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["\r\n", 2],
                'matches'   => [["\r\n", 0]],
                'expect'    => [
                    'result' => true,
                    'value' => "\r\n",
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 2,
                            'sourceOffset' => 2,
                            'sourceCharOffset' => 2
                        ]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["xz", 2],
                'matches'   => [],
                'expect'    => [
                    'result' => false,
                    'value' => null,
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 2,
                            'sourceOffset' => 2,
                            'sourceCharOffset' => 2
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 2,
                                'sourceCharOffset' => 2,
                                'message' => 'internal error: missing or invalid capture group 0'
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["xz", 2],
                'matches'   => [[null,-1]],
                'expect'    => [
                    'result' => false,
                    'value' => null,
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 2,
                            'sourceOffset' => 2,
                            'sourceCharOffset' => 2
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 2,
                                'sourceCharOffset' => 2,
                                'message' => 'internal error: missing or invalid capture group 0'
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ]
        ];
    }

    /**
     * @dataProvider parseMatched__cases
     */
    public function test__parseMatched(array $source, array $matches, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        if ($expect['init'] ?? null) {
            $value = $expect['init'];
        }

        $rule = new SepRule();

        $result = $rule->parseMatched($state, $matches, $value);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['value'], $value);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }

    //
    // parse()
    //

    public static function parse__cases()
    {
        return [
            [
                'source'    => ["\n", 0],
                'args'      => [],
                'expect'    => [
                    'result' => true,
                    'value' => "\n",
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 1,
                            'sourceOffset' => 1,
                            'sourceCharOffset' => 1
                        ]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["\r\n", 0],
                'args'      => [],
                'expect'    => [
                    'result' => true,
                    'value' => "\r\n",
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 2,
                            'sourceOffset' => 2,
                            'sourceCharOffset' => 2
                        ]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["xz", 0],
                'args'      => [],
                'expect'    => [
                    'result' => false,
                    'value' => null,
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                            'sourceOffset' => 0,
                            'sourceCharOffset' => 0
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 0,
                                'sourceCharOffset' => 0,
                                'message' => 'syntax error: expected line separator (RFC2849)'
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            [
                'source'    => ["xz", 0],
                'args'      => [true],
                'expect'    => [
                    'result' => false,
                    'value' => null,
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                            'sourceOffset' => 0,
                            'sourceCharOffset' => 0
                        ]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider parse__cases
     */
    public function test__parse(array $source, array $args, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        if (array_key_exists('init', $expect)) {
            $value = $expect['init'];
        }

        $rule = new SepRule;

        $result = $rule->parse($state, $value, ...$args);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['value'], $value);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
