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

use Korowai\Lib\Ldif\Rules\LdifAttrValRecordRule;
use Korowai\Lib\Ldif\Rules\AbstractLdifRecordRule;
use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Lib\Ldif\Rules\DnSpecRule;
use Korowai\Lib\Ldif\Rules\SepRule;
use Korowai\Lib\Ldif\Rules\AttrValSpecRule;
use Korowai\Lib\Ldif\Nodes\AttrValRecordInterface;
use Korowai\Lib\Ldif\Exception\InvalidRuleClassException;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class LdifAttrValRecordRuleTest extends TestCase
{
    public function test__extends__AbstractLdifRecordRule()
    {
        $this->assertExtendsClass(AbstractLdifRecordRule::class, LdifAttrValRecordRule::class);
    }

    public static function construct__cases()
    {
        $dnSpecRule = new DnSpecRule;
        $sepRule = new SepRule;
        $attrValSpecRule = new AttrValSpecRule;

        return [
            '__construct()' => [
                'args'   => [],
                'expect' => [
                ]
            ],
            '__construct([...])' => [
                'args'   => [[
                    'dnSpecRule' => $dnSpecRule,
                    'sepRule' => $sepRule,
                    'attrValSpecRule' => $attrValSpecRule,
                ]],
                'expect' => [
                    'dnSpecRule' => $dnSpecRule,
                    'sepRule' => $sepRule,
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
        $rule = new LdifAttrValRecordRule(...$args);
        $this->assertInstanceOf(DnSpecRule::class, $rule->getDnSpecRule());
        $this->assertInstanceOf(SepRule::class, $rule->getSepRule());
        $this->assertInstanceOf(AttrValSpecRule::class, $rule->getAttrValSpecRule());
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    //
    // parse()
    //

    public static function parse__cases()
    {
        return [
            #0
            [
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
                                'message' => 'syntax error: expected "dn:" (RFC2849)',
                            ])
                        ],
                        'records' => []
                    ],
                ]
            ],
            #1
            [
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
            #2
            [
                //            00000000001111111111222222222233333
                //            01234567890123456789012345678901234
                'source' => ['foo: ', 0],
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
                                'message' => 'syntax error: expected "dn:" (RFC2849)',
                            ])
                        ],
                        'records' => []
                    ],
                ]
            ],
            #3
            [
                //            000000000011111111112 22222222233333
                //            012345678901234567890 12345678901234
                'source' => ["dn: dc=example,dc=org\n", 0],
                'args' => [true],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 22]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 22,
                                'message' => 'syntax error: expected <AttributeDescription>":" (RFC2849)',
                            ])
                        ],
                        'records' => []
                    ],
                ]
            ],
            #4
            [
                //            000000000011111111112 22222222233333
                //            012345678901234567890 12345678901234
                'source' => ["dn: dc=example,dc=org\ndc", 0],
                'args' => [true],
                'expect' => [
                    'init' => true,
                    'result' => false,
                    'value' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 22]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 22,
                                'message' => 'syntax error: expected <AttributeDescription>":" (RFC2849)',
                            ])
                        ],
                        'records' => []
                    ],
                ]
            ],
            #5
            [
                //            000000000011111111112 22222 222233333
                //            012345678901234567890 12345 678901234
                'source' => ["dn: dc=example,dc=org\ndc: \n", 0],
                'args' => [true],
                'expect' => [
                    'init' => false,
                    'result' => true,
                    'value' => [
                        'dn' => 'dc=example,dc=org',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'dc',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => '',
                                    'content' => ''
                                ]),
                            ]),
                        ],
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 27]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            #6
            [
                //            000000000011111111112 222222222333 33
                //            012345678901234567890 123456789012 34
                'source' => ["dn: dc=example,dc=org\ndc: example\n", 0],
                'args' => [true],
                'expect' => [
                    'init' => false,
                    'result' => true,
                    'value' => [
                        'dn' => 'dc=example,dc=org',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'dc',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'example',
                                    'content' => 'example'
                                ]),
                            ]),
                        ],
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 34]),
                        'errors' => [],
                        'records' => []
                    ],
                ]
            ],
            #7
            [
                //            000000000011111111112 222222222333 33333334444444444555555 55
                //            012345678901234567890 123456789012 34567890123456789012345 67
                'source' => ["dn: dc=example,dc=org\ndc: example\ncomment:: xbzDs8WCdGtv\n", 0],
                'args' => [true],
                'expect' => [
                    'init' => false,
                    'result' => true,
                    'value' => [
                        'dn' => 'dc=example,dc=org',
                        'attrValSpecs' => [
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'dc',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_SAFE,
                                    'spec' => 'example',
                                    'content' => 'example'
                                ]),
                            ]),
                            self::hasPropertiesIdenticalTo([
                                'attribute' => 'comment',
                                'valueObject' => self::hasPropertiesIdenticalTo([
                                    'type' => ValueInterface::TYPE_BASE64,
                                    'spec' => 'xbzDs8WCdGtv',
                                    'content' => 'żółtko'
                                ]),
                            ]),
                        ],
                    ],
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo(['offset' => 57]),
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

        if ($expect['init'] ?? null) {
            $value = $this->getMockBuilder(AttrValRecordInterface::class)->getMockForAbstractClass();
        }

        $rule = new LdifAttrValRecordRule();

        $result = $rule->parse($state, $value, ...$args);

        $this->assertSame($expect['result'], $result);

        if (is_array($expect['value'])) {
            $this->assertImplementsInterface(AttrValRecordInterface::class, $value);
            $this->assertHasPropertiesSameAs($expect['value'], $value);
        } else {
            $this->assertSame($expect['value'], $value);
        }
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
