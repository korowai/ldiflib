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

use Korowai\Lib\Ldif\Rules\AttrValSpecRule;
use Korowai\Lib\Ldif\Rules\ValueSpecRule;
use Korowai\Lib\Ldif\Rules\AbstractRfcRule;
use Korowai\Lib\Ldif\AttrValInterface;
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Lib\Rfc\Rfc2849;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AttrValSpecRuleTest extends TestCase
{
    public function test__extendsAbstractRfcRule()
    {
        $this->assertExtendsClass(AbstractRfcRule::class, AttrValSpecRule::class);
    }

    public static function construct__cases()
    {
        $valueSpecRule = new ValueSpecRule;

        return [
            'default' => [
                'args'   => [],
                'expect' => []
            ],
            'valueSpecRule' => [
                'args'   => [$valueSpecRule],
                'expect' => ['valueSpecRule' => $valueSpecRule],
            ]
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = new AttrValSpecRule(...$args);

        $expect = array_merge([
            'rfcRule' => self::hasPropertiesIdenticalTo([
                'ruleSetClass' => Rfc2849::class,
                'name' => 'ATTRVAL_SPEC',
            ])
        ], $expect);

        $this->assertHasPropertiesSameAs($expect, $rule);

        if (null === ($expect['valueSpecRule'] ?? null)) {
            $this->assertInstanceOf(ValueSpecRule::class, $rule->getValueSpecRule());
        }
    }

    public function test__valueSpecRule()
    {
        $rule = new AttrValSpecRule;
        $vsRule = new ValueSpecRule;

        $this->assertNotNull($rule->getValueSpecRule());
        $this->assertSame($rule, $rule->setValueSpecRule($vsRule));
        $this->assertSame($vsRule, $rule->getValueSpecRule());
    }

    //
    // parseMatched()
    //
    public static function parseMatched__cases()
    {
        return [
            'valid' => [
                'source' => ['attrType;lang-pl: AAA', 21],
                'matches' => [
                    'attr_desc' => ['attrType;lang-pl', 0],
                    'value_safe' => ['AAA', 18]
                ],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'attribute' => 'attrType;lang-pl',
                        'valueObject' => self::hasPropertiesIdenticalTo([
                            'type' => ValueInterface::TYPE_SAFE,
                            'spec' => 'AAA',
                            'content' => 'AAA'
                        ]),
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 21]),
                        'errors' => [],
                        'records' => [],
                    ]
                ]
            ],
            'invalid_base64' => [
                'source' => ['attrType:: R', 12],
                'matches' => [
                    'attr_desc' => ['attrType', 0],
                    'value_b64' => ['R', 11]
                ],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 12]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 11,
                                'message' => 'syntax error: invalid BASE64 string',
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ],
            'missing attr_desc' => [
                'source' => ['AAA', 21],
                'matches' => [
                    'value_safe' => ['AAA', 18],
                ],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 21]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 21,
                                'message' => 'internal error: missing or invalid capture group "attr_desc"'
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
            $value = $this->getMockBuilder(AttrValInterface::class)->getMockForAbstractClass();
        }

        $rule = new AttrValSpecRule();

        $result = $rule->parseMatched($state, $matches, $value);

        $this->assertSame($expect['result'], $result);
        if (is_array($expect['value'])) {
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
            'empty string' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 0]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 0,
                                'message' => 'syntax error: expected <AttributeDescription>":" (RFC2849)',
                            ])
                        ],
                        'records' => []
                    ],
                ]
            ],
            'empty string (tryOnly)' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['', 0],
                'args' => [true],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 0]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'broken AttributeDescription (tryOnly)' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['attrType;: FOO', 0],
                'args' => [true],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 0]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'missing value-spec' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['attrType', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 0]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 0,
                                'message' => 'syntax error: expected <AttributeDescription>":" (RFC2849)',
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            'missing value-spec (tryOnly)' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['attrType', 0],
                'args' => [true],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 0]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'attrType: <value_safe>' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['attrType: FOO', 0],
                'args' => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'attribute' => 'attrType',
                        'valueObject' => self::hasPropertiesIdenticalTo([
                            'type' => ValueInterface::TYPE_SAFE,
                            'spec' => 'FOO',
                            'content' => 'FOO',
                        ])
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 13]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'attrType;option-1: <value_safe>' => [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['attrType;option-1: FOO', 0],
                'args' => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'attribute' => 'attrType;option-1',
                        'valueObject' => self::hasPropertiesIdenticalTo([
                            'type' => ValueInterface::TYPE_SAFE,
                            'spec' => 'FOO',
                            'content' => 'FOO',
                        ])
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 22]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'attrType: <value_safe_error>' => [
                //            0000000000111111111222222222233333
                //            0123456789012356789012345678901234
                'source' => ['attrType: FOOŁXXX', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 18]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 13,
                                'message' => 'syntax error: malformed SAFE-STRING (RFC2849)',
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            'attrType:: <value_b64>' => [
                //            000000000011111111112222222222333333
                //            012345678901234567890123456789012345
                'source' => ['attrType:: xbvDs8WCdGEgxYHDs2TFug==', 0],
                'args' => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'attribute' => 'attrType',
                        'valueObject' => self::hasPropertiesIdenticalTo([
                            'type' => ValueInterface::TYPE_BASE64,
                            'spec' => 'xbvDs8WCdGEgxYHDs2TFug==',
                            'content' => 'Żółta Łódź',
                        ])
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 35]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'attrType:: <value_b64_error>' => [
                //            00000000001111111112222222222333333
                //            01234567890123457890123456789012345
                'source' => ['attrType:: xbvDł8W', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 19]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 15,
                                'message' => 'syntax error: malformed BASE64-STRING (RFC2849)'
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            'attrType:: <value_b64_invalid>' => [
                //            00000000001111111112222222222333333
                //            01234567890123457890123456789012345
                'source' => ['attrType:: R', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 12]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 11,
                                'message' => 'syntax error: invalid BASE64 string'
                            ]),
                        ],
                        'records' => []
                    ],
                ]
            ],
            'attrType:< <value_url>' => [
                //            000000000011111111112222222222333333333
                //            012345678901234567890123456789012345678
                'source' => ['attrType:< file:///home/jsmith/foo.txt', 0],
                'args' => [],
                'expect' => [
                    'result' => true,
                    'value' => [
                        'attribute' => 'attrType',
                        'valueObject' => self::hasPropertiesIdenticalTo([
                            'type' => ValueInterface::TYPE_URL,
                            'spec' => self::hasPropertiesIdenticalTo([
                                'string' => 'file:///home/jsmith/foo.txt',
                                'scheme' => 'file',
                                'authority' => '',
                                'userinfo' => null,
                                'host' => '',
                                'port' => null,
                                'path' => '/home/jsmith/foo.txt',
                                'query' => null,
                                'fragment' => null,
                            ])
                            //'value_url' => 'file:///home/jsmith/foo.txt',
                        ]),
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 38]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            'attrType:< <value_url_error>' => [
                //            000000000011111111112222222222333333333
                //            012345678901234567890123456789012345678
                'source' => ['attrType:< ##', 0],
                'args' => [],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 13]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 12,
                                'message' => 'syntax error: malformed URL (RFC2849/RFC3986)',
                            ])
                        ],
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

        if ($expect['init'] ?? null) {
            $value = $this->getMockBuilder(AttrValInterface::class)->getMockForAbstractClass();
        }

        $rule = new AttrValSpecRule;

        $result = $rule->parse($state, $value, ...$args);

        $this->assertSame($expect['result'], $result);

        if (is_array($expect['value'])) {
            $this->assertHasPropertiesSameAs($expect['value'], $value);
        } else {
            $this->assertSame($expect['value'], $value);
        }
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
