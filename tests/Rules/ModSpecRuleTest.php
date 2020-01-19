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

use Korowai\Lib\Ldif\Rules\ModSpecRule;
use Korowai\Lib\Ldif\Rules\AbstractRule;
use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Ldif\Rules\ModSpecInitRule;
use Korowai\Lib\Ldif\Rules\AttrValSpecRule;
use Korowai\Lib\Ldif\ModSpecInterface;
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModSpecRuleTest extends TestCase
{
    public function test__extends__AbstractRule()
    {
        $this->assertExtendsClass(AbstractRule::class, ModSpecRule::class);
    }

    public static function construct__cases()
    {
        $modSpecInitRule = new ModSpecInitRule;
        $attrValSpecRule = new AttrValSpecRule;

        return [
            '__construct()' => [
                'args'   => [],
                'expect' => [
                ]
            ],
            '__construct([...])' => [
                'args'   => [[
                    'modSpecInitRule' => $modSpecInitRule,
                    'attrValSpecRule' => $attrValSpecRule,
                ]],
                'expect' => [
                    'modSpecInitRule' => $modSpecInitRule,
                    'attrValSpecRule' => $attrValSpecRule,
                ]
            ],
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = new ModSpecRule(...$args);
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    //
    // parse()
    //

    public static function parse__cases()
    {
        return [
            'add: cn\n-' => [
                //            0000000 00011111111112222222
                //            0123456 78901234567890123456
                'source' => ["add: cn\n-", 0],
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
                            'offset' => 9,
                            'sourceOffset' => 9,
                            'sourceCharOffset' => 9
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'add: cn\ncn: foo\n-' => [
                //            0000000 00011111 111112222222
                //            0123456 78901234 567890123456
                'source' => ["add: cn\ncn: foo\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'add',
                        'attribute' => 'cn',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'cn',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'foo',
                                    'content' => 'foo'
                                ])
                            ])
                        ]
                    ],
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
            'add: cn\ncn: foo\ncn:: YmFy\n-' => [
                //            0000000 00011111 1111122222 222
                //            0123456 78901234 5678901234 567
                'source' => ["add: cn\ncn: foo\ncn:: YmFy\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'add',
                        'attribute' => 'cn',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'cn',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'foo',
                                    'content' => 'foo'
                                ])
                            ]),
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'cn',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_BASE64,
                                    'spec' => 'YmFy',
                                    'content' => 'bar'
                                ])
                            ])
                        ]
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 27,
                            'sourceOffset' => 27,
                            'sourceCharOffset' => 27
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'delete: cn\n-' => [
                //            0000000000 11111111112222222
                //            0123456789 01234567890123456
                'source' => ["delete: cn\n-", 0],
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
                            'offset' => 12,
                            'sourceOffset' => 12,
                            'sourceCharOffset' => 12
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'replace: cn\n-' => [
                //            00000000001 1111111112222222
                //            01234567890 1234567890123456
                'source' => ["replace: cn\n-", 0],
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
                            'offset' => 13,
                            'sourceOffset' => 13,
                            'sourceCharOffset' => 13
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'replace: cn\ncn: foo\n-' => [
                //            00000000001 11111111 12222222
                //            01234567890 12345678 90123456
                'source' => ["replace: cn\ncn: foo\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'modType' => 'replace',
                        'attribute' => 'cn',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'cn',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'foo',
                                    'content' => 'foo'
                                ])
                            ])
                        ]
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 21,
                            'sourceOffset' => 21,
                            'sourceCharOffset' => 21
                        ]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'foo: cn\n-' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ["foo: cn\n-", 0],
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
            'foo: cn\n- (trying)' => [
                //            000000000011111111112222222
                //            012345678901234567890123456
                'source' => ["foo: cn\n-", 0],
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
            'add: \n-' => [
                //            00000 0000011111111112222222
                //            01234 5678901234567890123456
                'source' => ["add: \n-", 0],
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
            'add: atłybut\n-' => [
                //            000000000111 111111122222222
                //            012345679012 345678901234567
                'source' => ["add: atłybut\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 14,
                            'sourceOffset' => 14,
                            'sourceCharOffset' => 13
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
            'add: ;\n-' => [
                //            000000 000011111111112222222
                //            012345 678901234567890123456
                'source' => ["add: ;\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 7,
                            'sourceOffset' => 7,
                            'sourceCharOffset' => 7
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
            'add: cn;\n-' => [
                //            00000000 0011111111112222222
                //            01234567 8901234567890123456
                'source' => ["add: cn;\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 9,
                            'sourceOffset' => 9,
                            'sourceCharOffset' => 9
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
            'add: cn;błąd\n-' => [
                //            000000000011 1111112222222
                //            012345678913 4567890123456
                'source' => ["add: cn;błąd\n-", 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 15,
                            'sourceOffset' => 15,
                            'sourceCharOffset' => 13
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
            'add: cn\ncn: foo' => [
                //            0000000 000111111112222222
                //            0123456 789012345678901234
                'source' => ["add: cn\ncn: foo", 0],
                'args'   => [],
                'expect' => [
                    'result' => false,
                    'value' => [
                        'modType' => 'add',
                        'attribute' => 'cn',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'cn',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'foo',
                                    'content' => 'foo'
                                ])
                            ]),
                        ]
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 15,
                            'sourceOffset' => 15,
                            'sourceCharOffset' => 15
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 15,
                                'message' => 'syntax error: expected "-" followed by end of line'
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

        $rule = new ModSpecRule;

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
