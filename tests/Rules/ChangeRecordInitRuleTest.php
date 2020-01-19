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

use Korowai\Lib\Ldif\Rules\ChangeRecordInitRule;
use Korowai\Lib\Ldif\Rules\AbstractRfcRule;
use Korowai\Lib\Rfc\Rfc2849;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ChangeRecordInitRuleTest extends TestCase
{
    public function test__extendsAbstractRfcRule()
    {
        $this->assertExtendsClass(AbstractRfcRule::class, ChangeRecordInitRule::class);
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
        $rule = new ChangeRecordInitRule(...$args);
        $expect = array_merge([
            'rfcRule' => self::hasPropertiesIdenticalTo([
                'ruleSetClass' => Rfc2849::class,
                'name' => 'CHANGERECORD_INIT',
            ])
        ], $expect);
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    //
    // parseMatched()
    //
    public static function parseMatched__cases()
    {
        return [
            'changetype: add' => [
                //            0000000000111111
                //            0123456789012345
                'source' => ['changetype: add', 15],
                'matches' => [
                    'chg_type' => ['add', 12],
                ],
                'expect' => [
                    'result' => true,
                    'value' => 'add',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 15]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: delete' => [
                //            0000000000111111111
                //            0123456789012345678
                'source' => ['changetype: delete', 18],
                'matches' => [
                    'chg_type' => ['delete', 12],
                ],
                'expect' => [
                    'result' => true,
                    'value' => 'delete',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 18]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: moddn' => [
                //            0000000000111111111
                //            0123456789012345678
                'source' => ['changetype: moddn', 17],
                'matches' => [
                    'chg_type' => ['moddn', 12],
                ],
                'expect' => [
                    'result' => true,
                    'value' => 'moddn',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 17]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: modrdn' => [
                //            0000000000111111111
                //            0123456789012345678
                'source' => ['changetype: modrdn', 18],
                'matches' => [
                    'chg_type' => ['modrdn', 12],
                ],
                'expect' => [
                    'result' => true,
                    'value' => 'modrdn',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 18]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: modify' => [
                //            0000000000111111111
                //            0123456789012345678
                'source' => ['changetype: modify', 18],
                'matches' => [
                    'chg_type' => ['modify', 12],
                ],
                'expect' => [
                    'result' => true,
                    'value' => 'modify',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 18]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: qux' => [
                //            0000000000111111111
                //            0123456789012345678
                'source' => ['changetype: qux', 15],
                'matches' => [
                    'chg_type' => ['qux', 12],
                ],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 15]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 12,
                                'message' => 'syntax error: unsupported change type: "qux"'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'missing chg_type' => [
                //            0000000000111111
                //            0123456789012345
                'source' => ['changetype: ___', 15],
                'matches' => [
                ],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 15]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 15,
                                'message' => 'internal error: missing or invalid capture group "chg_type"',
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
        ];
    }

    /**
     * @dataProvider parseMatched__cases
     */
    public function test__parseMatched(array $source, array $matches, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        if ($expect['init'] ?? null) {
            $value = $this->getMockBuilder(ModSpecInterface::class)->getMockForAbstractClass();
        }

        $rule = new ChangeRecordInitRule();

        $result = $rule->parseMatched($state, $matches, $value);

        $this->assertSame($expect['result'], $result);
        if (is_array($expect['value'])) {
            $this->assertInstanceOf(ModSpecInterface::class, $value);
            $this->assertHasPropertiesSameAs($expect['value'], $value);
        } else {
            $this->assertSame($expect['value'], $value);
        }
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }

    //
    // parse()
    //

    public static function parse__cases()
    {
        return [
            'changetype: add' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: add', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => 'add',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 15,
                            'sourceOffset' => 15,
                            'sourceCharOffset' => 15
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: delete' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: delete', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => 'delete',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 18,
                            'sourceOffset' => 18,
                            'sourceCharOffset' => 18
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: moddn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: moddn', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => 'moddn',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 17,
                            'sourceOffset' => 17,
                            'sourceCharOffset' => 17
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: modrdn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: modrdn', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => 'modrdn',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 18,
                            'sourceOffset' => 18,
                            'sourceCharOffset' => 18
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: modify' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: modify', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => 'modify',
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 18,
                            'sourceOffset' => 18,
                            'sourceCharOffset' => 18
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'foo: add' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['foo: add', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                            'sourceOffset' => 0,
                            'sourceCharOffset' => 0
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 0,
                                'message' => 'syntax error: expected "changetype:" (RFC2849)',
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'foo: add (tryOnly)' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['foo: add', 0],
                'args'   => [true],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                            'sourceOffset' => 0,
                            'sourceCharOffset' => 0
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: ' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['changetype: ', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 12,
                            'sourceOffset' => 12,
                            'sourceCharOffset' => 12
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 12,
                                'message' => 'syntax error: missing or invalid change type (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'changetype: foo' => [
                //            0000000001111111111222222222
                //            0123456789012345678901234567
                'source' => ['changetype: foo', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 15,
                            'sourceOffset' => 15,
                            'sourceCharOffset' => 15
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 12,
                                'message' => 'syntax error: missing or invalid change type (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
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

        if ($expect['init'] ?? null) {
            $value = $this->getMockBuilder(ModSpecInterface::class)->getMockForAbstractClass();
        }

        $rule = new ChangeRecordInitRule;

        $result = $rule->parse($state, $value, ...$args);

        $this->assertSame($expect['result'], $result);

        if (is_array($expect['value'])) {
            $this->assertInstanceOf(ModSpecInterface::class, $value);
            $this->assertHasPropertiesSameAs($expect['value'], $value);
        } else {
            $this->assertSame($expect['value'], $value);
        }
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
