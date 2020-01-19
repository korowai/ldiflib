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

use Korowai\Lib\Ldif\Rules\DnSpecRule;
use Korowai\Lib\Ldif\Rules\AbstractDnSpecRule;
use Korowai\Lib\Ldif\Rules\Util;
use Korowai\Lib\Rfc\Rfc2849;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class DnSpecRuleTest extends TestCase
{
    public function test__extendsAbstractNameSpecRule()
    {
        $this->assertExtendsClass(AbstractDnSpecRule::class, DnSpecRule::class);
    }

    public static function construct__cases()
    {
        return [
            '__construct()' => [
                'args'   => [],
                'expect' => [
                    'rfcRule' => self::hasPropertiesIdenticalTo([
                        'ruleSetClass' => Rfc2849::class,
                        'name' => 'DN_SPEC',
                    ]),
                ]
            ],
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = new DnSpecRule(...$args);
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
        $safeStringCases = array_map(function ($case) {
            $tl = strlen('dn: ');
            $dn = $case[0];
            $result = $case[1];
            //          0234567
            $source = ['ł dn: '.$dn, 3 + strlen('dn: ') + strlen($dn)];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('dn: '),
                    'sourceCharOffset' => 2 + mb_strlen('dn: '),
                    'message' => 'syntax error: invalid DN syntax: "'.$dn.'"',
                ])
            ];
            $matches = [[$dn, 3 + strlen('dn: ')], 'value_safe' => [$dn, 3 + strlen('dn: ')]];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('dn: ') + strlen($dn),
                'sourceOffset' => 3 + strlen('dn: ') + strlen($dn),
                'sourceCharOffset' => 2 + mb_strlen('dn: ') + mb_strlen($dn),
            ]);
            $expect = [
                'result' => $result,
                'dn' => $result ? $dn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, static::dnMatch__cases());

        $base64StringCases = array_map(function ($case) {
            $dn = $case[0];
            $dnBase64 = base64_encode($dn);
            $result = $case[1];
            //          0234567
            $source = ['ł dn:: '.$dnBase64, 3 + strlen('dn:: ') + strlen($dnBase64)];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('dn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('dn:: '),
                    'message' => 'syntax error: invalid DN syntax: "'.$dn.'"',
                ]),
            ];
            $matches = [[$dnBase64, 3 + strlen('dn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('dn:: ')]];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('dn:: ') + strlen($dnBase64),
                'sourceOffset' => 3 + strlen('dn:: ') + strlen($dnBase64),
                'sourceCharOffset' => 2 + mb_strlen('dn:: ') + mb_strlen($dnBase64),
            ]);
            $expect = [
                'result' => $result,
                'dn' => $result ? $dn : null,
                'state' => [
                    'cursor' => $cursor,
                    'errors' => $errors,
                    'records' => [],
                ],
            ];

            return [$source, $matches, $expect];
        }, static::dnMatch__cases());

        $invalidBase64StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł dn:: '.$dnBase64, 3 + strlen('dn:: ') + $case['offset']];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('dn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('dn:: '),
                    'message' => 'syntax error: invalid BASE64 string',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('dn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('dn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + mb_strlen('dn:: ') + $case['offset'],
            ]);
            $matches = [[$dnBase64, 3 + strlen('dn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('dn:: ')]];
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'dn' => null,
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
            $source = ['ł dn:: '.$dnBase64, 3 + strlen('dn:: ') + $case['offset']];
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + strlen('dn:: '),
                    'sourceCharOffset' => 2 + mb_strlen('dn:: '),
                    'message' => 'syntax error: the string is not a valid UTF8',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + strlen('dn:: ') + $case['offset'],
                'sourceOffset' => 3 + strlen('dn:: ') + $case['offset'],
                'sourceCharOffset' => 2 + mb_strlen('dn:: ') + $case['charOffset'],
            ]);
            $matches = [[$dnBase64, 3 + strlen('dn:: ')], 'value_b64' => [$dnBase64, 3 + strlen('dn:: ')]];
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'dn' => $result ? $case['dn'] : null,
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
            ["YXNkgGZm", 'offset' => 8, 'charOffset' => 8, 'dn' => "asd\x80ff"],
        ]);

        $malformedStringCases = array_map(function ($case) {
            $sep = $case[0];
            $dn = $case[1];
            $result = false;
            //          0123456
            $source = ['dn:'.$sep.$dn];
            $source[] = strlen($source[0]);
            $type = substr($sep, 0, 1) === ':' ? 'BASE64': 'SAFE';
            $message = $type === 'BASE64' ? 'invalid BASE64 string' : 'invalid DN syntax: "'.$dn.'"';
            $dnOffset = strlen('dn:'.$sep) + $case[2];
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
            $matches = [[$dn, $dnOffset], $dnKey => [$dn, $dnOffset]];

            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'dn' => null,
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
                    'dn'     => null,
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
            $dn = $expect['init'];
        }

        $rule = new DnSpecRule();

        $result = $rule->parseMatched($state, $matches, $dn);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['dn'], $dn);
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
                    'message' => 'syntax error: expected "dn:" (RFC2849)',
                ]),
            ];
            return [
                'source' => $case[0],
                'args'   => $args,
                'expect' => [
                    'result' => false,
                    'init' => 'preset string',
                    'dn' => null,
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
            [["ł dn :", 3],     'offset' => 3, 'charOffset' => 2],
            [["ł dn\n:", 3],    'offset' => 3, 'charOffset' => 2],
        ]);


        $safeStringCases = array_map(function ($case) {
            $dn = $case[0];
            $result = $case[1];
            //          0234567
            $source = ['ł dn: '.$dn, 3];
            // the 4 below is from strlen('dn: ')
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + 4,
                    'sourceCharOffset' => 2 + 4,
                    'message' => 'syntax error: invalid DN syntax: "'.$dn.'"',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + 4 + strlen($dn),
                'sourceOffset' => 3 + 4 + strlen($dn),
                'sourceCharOffset' => 2 + 4 + mb_strlen($dn),
            ]);
            $expect = [
                'result' => $result,
                'dn' => $result ? $dn : null,
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
        }, static::dnMatch__cases());

        $base64StringCases = array_map(function ($case) {
            $dn = $case[0];
            $dnBase64 = base64_encode($dn);
            $result = $case[1];
            //          0234567
            $source = ['ł dn:: '.$dnBase64, 3];
            // the 5 below is from strlen('dn:: ')
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + 5,
                    'sourceCharOffset' => 2 + 5,
                    'message' => 'syntax error: invalid DN syntax: "'.$dn.'"',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + 5 + strlen($dnBase64),
                'sourceOffset' => 3 + 5 + strlen($dnBase64),
                'sourceCharOffset' => 2 + 5 + mb_strlen($dnBase64),
            ]);
            $expect = [
                'result' => $result,
                'dn' => $result ? $dn : null,
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
        }, static::dnMatch__cases());

        $invalidBase64StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł dn:: '.$dnBase64, 3];
            // the 5 below is from strlen('dn:: ')
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + 5,
                    'sourceCharOffset' => 2 + 5,
                    'message' => 'syntax error: invalid BASE64 string',
                ]),
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + 5 + $case['offset'],
                'sourceOffset' => 3 + 5 + $case['offset'],
                'sourceCharOffset' => 2 + 5 + $case['offset'],
            ]);
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'dn' => null,
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
            ["Zm9vgA=\n", 'offset' => 7, 'charOffset' => 7],
        ]);

        $base64InvalidUtf8StringCases = array_map(function ($case) {
            $dnBase64 = $case[0];
            $result = false;
            //          02345678
            $source = ['ł dn:: '.$dnBase64, 3];
            // the 5 below is from strlen('dn:: ')
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => 3 + 5,
                    'sourceCharOffset' => 2 + 5,
                    'message' => 'syntax error: the string is not a valid UTF8',
                ])
            ];
            $cursor = self::hasPropertiesIdenticalTo([
                'offset' => 3 + 5 + $case['offset'],
                'sourceOffset' => 3 + 5 + $case['offset'],
                'sourceCharOffset' => 2 + 5 + $case['charOffset'],
            ]);
            $expect = [
                'result' => $result,
                'init' => 'preset string',
                'dn' => null,
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
        //    00000000 0
        //    01234567 8
            ["YXNkgGZm\n", 'offset' => 8, 'charOffset' => 8, 'dn' => "asd\x80ff"],
        ]);

        $malformedStringCases = array_map(function ($case) {
            $sep = $case[0];
            $dn = $case[1];
            $result = false;
            //          0123456
            $source = ['dn:'.$sep.$dn, 0];
            $type = substr($sep, 0, 1) === ':' ? 'BASE64': 'SAFE';
            $message = 'malformed '.$type.'-STRING (RFC2849)';
            $errors = $result ? [] : [
                self::hasPropertiesIdenticalTo([
                    'sourceOffset' => strlen('dn:'.$sep) + $case[2],
                    'sourceCharOffset' => strlen('dn:'.$sep) + $case[2],
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
                'dn' => null,
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
            $dn = $expect['init'];
        }

        $rule = new DnSpecRule;

        $result = $rule->parse($state, $dn, ...$args);

        $this->assertSame($expect['result'], $result);
        $this->assertSame($expect['dn'], $dn);
        $this->assertHasPropertiesSameAs($expect['state'], $state);
    }
}

// vim: syntax=php sw=4 ts=4 et:
