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

use Korowai\Lib\Ldif\Rules\ModSpecInitRule;
use Korowai\Lib\Ldif\Rules\AbstractRfcRule;
use Korowai\Lib\Ldif\ModSpecInterface;
use Korowai\Lib\Rfc\Rfc2849;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModSpecInitRuleTest extends TestCase
{
    public function test__extendsAbstractRfcRule()
    {
        $this->assertExtendsClass(AbstractRfcRule::class, ModSpecInitRule::class);
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
        $rule = new ModSpecInitRule(...$args);
        $expect = array_merge([
            'rfcRule' => self::hasPropertiesIdenticalTo([
                'ruleSetClass' => Rfc2849::class,
                'name' => 'MOD_SPEC_INIT',
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
            'add: cn' => [
                //            01234567
                'source' => ['add: cn', 7],
                'matches' => [
                    'mod_type' => ['add', 0],
                    'attr_desc' => ['cn', 5]
                ],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'add',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 7]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'delete: cn' => [
                //            00000000001
                //            01234567890
                'source' => ['delete: cn', 10],
                'matches' => [
                    'mod_type' => ['delete', 0],
                    'attr_desc' => ['cn', 8]
                ],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'delete',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 10]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'replace: cn' => [
                //            000000000011
                //            012345678901
                'source' => ['replace: cn', 11],
                'matches' => [
                    'mod_type' => ['replace', 0],
                    'attr_desc' => ['cn', 9]
                ],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'replace',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 11]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'qux: cn' => [
                //            00000000
                //            01234567
                'source' => ['qux: cn', 7],
                'matches' => [
                    'mod_type' => ['qux', 0],
                    'attr_desc' => ['cn', 5]
                ],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 7]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 0,
                                'message' => 'syntax error: invalid mod-spec type: "qux"'
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'missing mod_type' => [
                //            00000000
                //            01234567
                'source' => ['___: cn', 7],
                'matches' => [
                    'attr_desc' => ['cn', 5]
                ],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 7]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 7,
                                'message' => 'internal error: missing or invalid capture groups '.
                                             '"mod_type" or "attr_desc"'
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'missing attr_desc' => [
                //            00000000
                //            01234567
                'source' => ['add: __', 7],
                'matches' => [
                    'mod_type' => ['add', 0]
                ],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 7]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 7,
                                'message' => 'internal error: missing or invalid capture groups '.
                                             '"mod_type" or "attr_desc"'
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

        $rule = new ModSpecInitRule();

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
            'add: cn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['add: cn', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'add',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 7,
                            'sourceOffset' => 7,
                            'sourceCharOffset' => 7
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'delete: cn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['delete: cn', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'delete',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 10,
                            'sourceOffset' => 10,
                            'sourceCharOffset' => 10
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'replace: cn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['replace: cn', 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'replace',
                        'attribute' => 'cn',
                        'attrValSpecs' => []
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 11,
                            'sourceOffset' => 11,
                            'sourceCharOffset' => 11
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'foo: cn' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['foo: cn', 0],
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
                                'message' => 'syntax error: expected one of "add:", "delete:" or "replace:" (RFC2849)',
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'foo: cn (tryOnly)' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['foo: cn', 0],
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
            'add: ' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['add: ', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 5,
                            'sourceOffset' => 5,
                            'sourceCharOffset' => 5
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 5,
                                'message' => 'syntax error: missing or invalid AttributeType (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'add: atłybut' => [
                //            000000000111111111122222222
                //            012345679012345678901234567
                'source' => ['add: atłybut', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 13,
                            'sourceOffset' => 13,
                            'sourceCharOffset' => 12
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 7,
                                'message' => 'syntax error: missing or invalid AttributeType (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'add: ;' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['add: ;', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 6,
                            'sourceOffset' => 6,
                            'sourceCharOffset' => 6
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 5,
                                'message' => 'syntax error: missing or invalid AttributeType (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'add: cn;' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ['add: cn;', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 8,
                            'sourceOffset' => 8,
                            'sourceCharOffset' => 8
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 8,
                                'message' => 'syntax error: missing or invalid options (RFC2849)'
                            ])
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'add: cn;błąd' => [
                //            0000000000111111112222222
                //            0123456789134567890123456
                'source' => ['add: cn;błąd', 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 14,
                            'sourceOffset' => 14,
                            'sourceCharOffset' => 12
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 9,
                                'message' => 'syntax error: missing or invalid options (RFC2849)'
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

        $rule = new ModSpecInitRule;

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
