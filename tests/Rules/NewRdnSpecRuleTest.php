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

use Korowai\Lib\Ldif\Rules\NewRdnSpecRule;
use Korowai\Lib\Ldif\Rules\AbstractRdnSpecRule;
use Korowai\Lib\Ldif\Rules\Util;
use Korowai\Lib\Rfc\Rfc2849;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class NewRdnSpecRuleTest extends TestCase
{
    public function test__extends__AbstractRdnSpecRule()
    {
        $this->assertExtendsClass(AbstractRdnSpecRule::class, NewRdnSpecRule::class);
    }

    public static function construct__cases()
    {
        return [
            '__construct()' => [
                'args'   => [],
                'expect' => [
                    'rfcRule' => self::hasPropertiesIdenticalTo([
                        'ruleSetClass' => Rfc2849::class,
                        'name' => 'NEWRDN_SPEC',
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
        $rule = new NewRdnSpecRule(...$args);
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    public static function rdnMatch__cases()
    {
        return UtilTest::rdnMatch__cases();
    }

    //
    // parseMatched()
    //
    public static function parseMatched__cases()
    {
        $safeStringCases = array_map(function ($case) {
            $rdn = $case[0];
            $result = $case[1];
            //          0234567
            $source = ['ł newrdn: '.$rdn, 3 + strlen('newrdn: ') + strlen($rdn)];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn: '),
                    'sourceCharOffset' => 2 + mb_strlen('newrdn: '),
                    'message' => 'syntax error: invalid RDN syntax: "'.$rdn.'"',
                ])
            ];
            $matches = [[$rdn, 3 + strlen('newrdn: ')], 'value_safe' => [$rdn, 3 + strlen('newrdn: ')]];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn: ') + strlen($rdn),
                'sourceOffset' => 3 + strlen('newrdn: ') + strlen($rdn),
                'sourceCharOffset' => 2 + mb_strlen('newrdn: ') + mb_strlen($rdn),
            ]);
            $expect = [
                'result' => $result,
                'rdn' => $result ? $rdn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, static::rdnMatch__cases());

        $base64StringCases = array_map(function ($case) {
            $rdn = $case[0];
            $dnBase64 = base64_encode($rdn);
            $result = $case[1];
            //          0234567
            $source = ['ł newrdn:: '.$dnBase64, 3 + strlen('newrdn:: ') + strlen($dnBase64)];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + strlen('newrdn:: '),
                    'message' => 'syntax error: invalid RDN syntax: "'.$rdn.'"',
                ]),
            ];
            $matches = [[$dnBase64, 3 + strlen('newrdn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('newrdn:: ')]];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + strlen($dnBase64),
                'sourceOffset' => 3 + strlen('newrdn:: ') + strlen($dnBase64),
                'sourceCharOffset' => 2 + strlen('newrdn:: ') + mb_strlen($dnBase64),
            ]);
            $expect = [
                'result' => $result,
                'rdn' => $result ? $rdn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, static::rdnMatch__cases());

        $invalidBase64StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł newrdn:: '.$dnBase64, 3 + strlen('newrdn:: ') + $case['offset']];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + strlen('newrdn:: '),
                    'message' => 'syntax error: invalid BASE64 string',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + strlen('newrdn:: ') + $case['offset'],
            ]);
            $matches = [[$dnBase64, 3 + strlen('newrdn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('newrdn:: ')]];
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, [
        //    0000000 00
        //    0123456 78
            ["Zm9vgA=\n", 'offset' => 7, 'charOffset' => 7],
        ]);

        $base64InvalidUtf8StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł newrdn:: '.$dnBase64, 3 + strlen('newrdn:: ') + $case['offset']];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + strlen('newrdn:: '),
                    'message' => 'syntax error: the string is not a valid UTF8',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + strlen('newrdn:: ') + $case['charOffset'],
            ]);
            $matches = [[$dnBase64, 3 + strlen('newrdn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('newrdn:: ')]];
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => $result ? $case['rdn'] : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, [
        //    00000000 0
        //    01234567 8
            ["YXNkgGZm", 'offset' => 8, 'charOffset' => 8, 'rdn' => "asd\x80ff"],
        ]);

        $malformedStringCases = array_map(function ($case) {
            $sep = $case[0];
            $rdn = $case[1];
            $result = false;
            //          0123456
            $source = ['newrdn:'.$sep.$rdn];
            $source[] = strlen($source[0]);
            $type = substr($sep, 0, 1) === ':' ? 'BASE64': 'SAFE';
            $message = $type === 'BASE64' ? 'invalid BASE64 string' : 'invalid RDN syntax: "'.$rdn.'"';
            $dnOffset = strlen('newrdn:'.$sep) + $case[2];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => $dnOffset,
                    'sourceCharOffset' => $dnOffset,
                    'message' => 'syntax error: '.$message,
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => strlen($source[0]),
                'sourceOffset' => strlen($source[0]),
                'sourceCharOffset' => mb_strlen($source[0]),
            ]);

            $dnKey = $type === 'BASE64' ? 'value_b64' : 'value_safe';
            $matches = [[$rdn, $dnOffset], $dnKey => [$rdn, $dnOffset]];

            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, [
            [' ',  ':sdf',     0],  // 1'st is not SAFE-INIT-CHAR (colon)
            [' ',  'tłuszcz',  1],  // 2'nd is not SAFE-CHAR (>0x7F)
            [':',  'tłuszcz',  1],  // 2'nd is not BASE64-CHAR
            [': ', 'Az@123=',  2],  // 3'rd is not BASE64-CHAR
        ]);

        $missingCaptureCases = array_map(function (array $matches) {
            return [
                //              0123456
                'source'  => ['x: O=1', 6],
                'matches' => $matches,
                'expect'   => [
                    'result' => false,
                    'init'   => 'preset string',
                    'rdn'     => null,
                    'state'  => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => 6,
                            'sourceOffset' => 6,
                            'sourceCharOffset' => 6,
                        ]),
                        'errors' => [
                            self::hasPropertiesIdenticalTo([
                                'sourceOffset' => 6,
                                'sourceCharOffset' => 6,
                                'message' => 'internal error: missing or invalid capture groups "value_safe" and "value_b64"'
                            ]),
                        ],
                        'records' => [],
                    ]
                ]
            ];
        }, [
            [],
            [[null,-1], 'value_safe' => [null,-1]],
            [[null,-1], 'value_b64'  => [null,-1]],
        ]);

        return array_merge(
            $safeStringCases,
            $base64StringCases,
            $invalidBase64StringCases,
            $base64InvalidUtf8StringCases,
            $malformedStringCases,
            $missingCaptureCases
        );
    }

    /**
     * @dataProvider parseMatched__cases
     */
    public function test__parseMatched(array $source, array $matches, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        if ($expect['init'] ?? null) {
            $rdn = $expect['init'];
        }

        $rule = new NewRdnSpecRule();

        $result = $rule->parseMatched($state, $matches, $rdn);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['rdn'], $rdn);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }

    //
    // parse()
    //

    public static function parse__cases()
    {
        $missingTagCases = array_map(function (array $case) {
            $args = $case['args'] ?? [];
            $optional = $args[0] ?? false;
            $errors = $optional ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => $case['offset'],
                    'sourceCharOffset' => $case['charOffset'],
                    'message' => 'syntax error: expected "newrdn:" (RFC2849)',
                ]),
            ];
            return [
                'source' => $case[0],
                'args'   => $args,
                'expect' => [
                    'result' => false,
                    'init' => 'preset string',
                    'rdn' => null,
                    'state' => [
                        'cursor' => self::hasPropertiesIdenticalTo([
                            'offset' => $case['offset'],
                            'sourceOffset' => $case['offset'],
                            'sourceCharOffset' => $case['charOffset']
                        ]),
                        'errors' => $errors,
                        'records' => [],
                    ],
                ]
            ];
        }, [
            [["ł ", 3],         'offset' => 3, 'charOffset' => 2],
            [["ł ", 3],         'offset' => 3, 'charOffset' => 2, 'args' => [false]],
            [["ł ", 3],         'offset' => 3, 'charOffset' => 2, 'args' => [true]],
            [["ł x", 3],        'offset' => 3, 'charOffset' => 2],
            [["ł dns:", 3],     'offset' => 3, 'charOffset' => 2],
            [["ł rdn :", 3],     'offset' => 3, 'charOffset' => 2],
            [["ł rdn\n:", 3],    'offset' => 3, 'charOffset' => 2],
        ]);


        $safeStringCases = array_map(function ($case) {
            $rdn = $case[0];
            $result = $case[1];
            //          0234567
            $source = ['ł newrdn: '.$rdn, 3];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn: '),
                    'sourceCharOffset' => 2 + mb_strlen('newrdn: '),
                    'message' => 'syntax error: invalid RDN syntax: "'.$rdn.'"',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn: ') + strlen($rdn),
                'sourceOffset' => 3 + strlen('newrdn: ') + strlen($rdn),
                'sourceCharOffset' => 2 + mb_strlen('newrdn: ') + mb_strlen($rdn),
            ]);
            $expect = [
                'result' => $result,
                'rdn' => $result ? $rdn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [
                'source' => $source,
                'args'   => [],
                'expect' => $expect
            ];
        }, static::rdnMatch__cases());

        $base64StringCases = array_map(function ($case) {
            $rdn = $case[0];
            $dnBase64 = base64_encode($rdn);
            $result = $case[1];
            //          0234567
            $source = ['ł newrdn:: '.$dnBase64, 3];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('newrdn:: '),
                    'message' => 'syntax error: invalid RDN syntax: "'.$rdn.'"',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + strlen($dnBase64),
                'sourceOffset' => 3 + strlen('newrdn:: ') + strlen($dnBase64),
                'sourceCharOffset' => 2 + mb_strlen('newrdn:: ') + mb_strlen($dnBase64),
            ]);
            $expect = [
                'result' => $result,
                'rdn' => $result ? $rdn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [
                'source' => $source,
                'args'   => [],
                'expect' => $expect
            ];
        }, static::rdnMatch__cases());

        $invalidBase64StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł newrdn:: '.$dnBase64, 3];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('newrdn:: '),
                    'message' => 'syntax error: invalid BASE64 string',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + mb_strlen('newrdn:: ') + $case['offset'],
            ]);
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [
                'source' => $source,
                'args'   => [],
                'expect' => $expect
            ];
        }, [
        //    0000000 00
        //    0123456 78
            ["Zm9vgA=\n", 'offset' => 8, 'charOffset' => 8],
        ]);

        $base64InvalidUtf8StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł newrdn:: '.$dnBase64, 3];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('newrdn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('newrdn:: '),
                    'message' => 'syntax error: the string is not a valid UTF8',
                ])
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('newrdn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + mb_strlen('newrdn:: ') + $case['charOffset'],
            ]);
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [
                'source' => $source,
                'args'   => [],
                'expect' => $expect
            ];
        }, [
        //    00000000 00
        //    01234567 89
            ["YXNkgGZm\n", 'offset' => 9, 'charOffset' => 9, 'rdn' => "asd\x80ff"],
        ]);

        $malformedStringCases = array_map(function ($case) {
            $sep = $case[0];
            $rdn = $case[1];
            $result = false;
            //          0123456
            $source = ['newrdn:'.$sep.$rdn, 0];
            $type = substr($sep, 0, 1) === ':' ? 'BASE64': 'SAFE';
            $message = 'malformed '.$type.'-STRING (RFC2849)';
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => strlen('newrdn:'.$sep) + $case[2],
                    'sourceCharOffset' => mb_strlen('newrdn:'.$sep) + $case[2],
                    'message' => 'syntax error: '.$message,
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => strlen($source[0]),
                'sourceOffset' => strlen($source[0]),
                'sourceCharOffset' => mb_strlen($source[0]),
            ]);
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'rdn' => null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [
                'source' => $source,
                'args'   => [],
                'expect' => $expect
            ];
        }, [
            [' ',  ':sdf',     0],  // 1'st is not SAFE-INIT-CHAR (colon)
            [' ',  'tłuszcz',  1],  // 2'nd is not SAFE-CHAR (>0x7F)
            [':',  'tłuszcz',  1],  // 2'nd is not BASE64-CHAR
            [': ', 'Az@123=',  2],  // 3'rd is not BASE64-CHAR
        ]);

        return array_merge(
            $missingTagCases,
            $safeStringCases,
            $base64StringCases,
            $invalidBase64StringCases,
            $base64InvalidUtf8StringCases,
            $malformedStringCases
        );
    }

    /**
     * @dataProvider parse__cases
     */
    public function test__parse(array $source, array $args, array $expect)
    {
        $state = $this->getParserStateFromSource(...$source);

        if (array_key_exists('init', $expect)) {
            $rdn = $expect['init'];
        }

        $rule = new NewRdnSpecRule;

        $result = $rule->parse($state, $rdn, ...$args);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['rdn'], $rdn);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
