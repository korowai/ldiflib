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

use Korowai\Lib\Ldif\Rules\AbstractRfcRule;
use Korowai\Lib\Ldif\Rules\AbstractRule;
use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Ldif\ParserStateInterface;
use Korowai\Testing\Rfclib\RuleSet1;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AbstractRfcRuleTest extends TestCase
{
    public function test__extends__AbstractRule()
    {
        $this->assertExtendsClass(AbstractRule::class, AbstractRfcRule::class);
    }

    public function test__implements__RfcRuleInterface()
    {
        $this->assertImplementsInterface(\Korowai\Lib\Rfc\RuleInterface::class, AbstractRfcRule::class);
    }

    public function test__uses__DecoratesRfcRuleInterface()
    {
        $this->assertUsesTrait(\Korowai\Lib\Rfc\Traits\DecoratesRuleInterface::class, AbstractRfcRule::class);
    }

    //
    // __construct()
    //
    public static function construct__cases()
    {
        return [
            '__construct(RuleSet1::clas, "ASSIGNMENT_INT")' => [
                'args' => [RuleSet1::class, "ASSIGNMENT_INT"],
                'expect' => [
                    'rfcRule' => self::hasPropertiesIdenticalTo([
                        'ruleSetClass' => RuleSet1::class,
                        'name' => 'ASSIGNMENT_INT',
                    ])
                ]
            ],
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = $this->getMockBuilder(AbstractRfcRule::class)
                     ->setConstructorArgs($args)
                     ->getMockForAbstractClass();

        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    //
    // match()
    //

    public static function match__cases()
    {
        return [
            // #0
            [
                'source'    => [''],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                        ]),
                        'records' => [],
                        'errors'  => [
                            self::hasPropertiesIdenticalTo([
                                'message' => 'syntax error: missing "var_name =" in integer assignment',
                                'sourceOffset' => 0
                            ]),
                        ],
                    ],
                    'matches' => [],
                ]
            ],
            // #1
            [
                'source'    => [''],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [true],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                        ]),
                        'records' => [],
                        'errors'  => [],
                    ],
                    'matches' => [],
                ]
            ],
            // #2
            [
                'source'    => ['var '],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                        ]),
                        'records' => [],
                        'errors'  => [
                            self::hasPropertiesIdenticalTo([
                                'message' => 'syntax error: missing "var_name =" in integer assignment',
                                'sourceOffset' => 0
                            ]),
                        ],
                    ],
                    'matches' => [
                        false,
                        'var_name'        => false,
                        'value_int'       => false,
                        'value_int_error' => false,
                    ],
                ]
            ],
            // #3
            [
                'source'    => ['var '],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [true],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 0,
                        ]),
                        'records' => [],
                        'errors'  => [],
                    ],
                    'matches' => [
                        false,
                        'var_name'        => false,
                        'value_int'       => false,
                        'value_int_error' => false,
                    ],
                ]
            ],
            // #4
            [
                'source'    => ['var = '],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 6,
                        ]),
                        'records' => [],
                        'errors'  => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 6,
                                'message' => 'syntax error: malformed integer value'
                            ]),
                        ],
                    ],
                    'matches' => [
                        ['var = ', 0],
                        'var_name'        => ['var', 0],
                        'value_int'       => false,
                        'value_int_error' => ['', 6],
                    ],
                ]
            ],
            // #5
            [
                'source'    => ['var = asd'],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 9,
                        ]),
                        'records' => [],
                        'errors'  => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 6,
                                'message' => 'syntax error: malformed integer value'
                            ]),
                        ],
                    ],
                    'matches' => [
                        ['var = asd', 0],
                        'var_name'        => ['var', 0],
                        'value_int'       => false,
                        'value_int_error' => ['asd', 6],
                    ],
                ]
            ],
            // #6
            [
                'source'    => ['var = 123'],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => false,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 9,
                        ]),
                        'records' => [],
                        'errors'  => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 9,
                                'message' => 'syntax error: malformed integer value'
                            ])
                        ],
                    ],
                    'matches' => [
                        ['var = 123', 0],
                        'var_name'        => ['var', 0],
                        'value_int'       => false,
                        'value_int_error' => ['', 9],
                    ],
                ]
            ],
            // #7
            [
                'source'    => ['var = 123;'],
                'args'      => [RuleSet1::class, 'ASSIGNMENT_INT'],
                'trying'    => [],
                'expect'    => [
                    'result' => true,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 10,
                        ]),
                        'records' => [],
                        'errors'  => [],
                    ],
                    'matches' => [
                        ['var = 123;', 0],
                        'var_name'        => ['var', 0],
                        'value_int'       => ['123', 6],
                        'value_int_error' => false,
                    ],
                ]
            ],
        ];
    }

    /**
     * @dataProvider match__cases
     */
    public function test__match(array $source, array $args, array $trying, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        $rule = $this->getMockBuilder(AbstractRfcRule::class)
                     ->setConstructorArgs($args)
                     ->setMethods(['parseMatched'])
                     ->getMockForAbstractClass();

        $rule->expects($this->never())
             ->method('parseMatched');

        $result = $rule->match($state, $matches, ...$trying);

        $this->assertSame($expect['result'] ?? true, $result);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
        $this->assertHasPregCaptures($expect['matches'], $matches);
    }

    //
    // parse()
    //

    /**
     * @dataProvider match__cases
     */
    public function test__parse(array $source, array $args, array $trying, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        $rule = $this->getMockBuilder(AbstractRfcRule::class)
                     ->setConstructorArgs($args)
                     ->setMethods(['parseMatched'])
                     ->getMockForAbstractClass();

        if ($expect['result']) {
            $rule->expects($this->once())
                 ->method('parseMatched')
                 ->with(
                     $this->identicalTo($state),
                     $this->hasPregCaptures($expect['matches'])
                 )
                 ->willReturn(true);
        } else {
            $rule->expects($this->never())
                 ->method('parseMatched');
        }

        $result = $rule->parse($state, $value, ...$trying);

        $this->assertSame($expect['result'] ?? true, $result);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
