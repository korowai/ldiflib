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

use Korowai\Lib\Ldif\Preprocessor;
use Korowai\Lib\Ldif\PreprocessorInterface;
use Korowai\Lib\Ldif\Input;
use Korowai\Lib\Ldif\Util\IndexMap;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class PreprocessorTest extends TestCase
{
    public function test__implements__PreprocessorInterface()
    {
        $this->assertImplementsInterface(PreprocessorInterface::class, Preprocessor::class);
    }

    public function rmReCases()
    {
        return [
            [
                '/foo/',
                '',
                '',
                new IndexMap([])
            ],

            [
                '/foo/',
                'bar baz',
                'bar baz',
                new IndexMap([[0,0]])
            ],

            [
                '/\n /m',
            //   00000 000011111 11111122
            //   01234 567890123 45678901
                "first\n  second\n  third",
                'first second third',
                new IndexMap([[0,0], [5,7], [12,16]])
            ],

            [
                '/\n /m',
            //   00000000001 111111 111222222 22223333 3 33333444444 4444555555
            //   01234567890 123456 789012345 67890123 4 56789012345 6789012345
                "# comment 1\nfirst\n  second\n  third\n\n# two-line\n  comment",
                "# comment 1\nfirst second third\n\n# two-line comment",
                new IndexMap([[0,0], [17,19], [24,28], [42,48]]),
            ],

            [
                '/^#[^\n]*\n?/m',
            //   00000000001 111111 111222222 22223333 3 33333444444 4444555555
            //   01234567890 123456 789012345 67890123 4 56789012345 6789012345
                "# comment 1\nfirst\n  second\n  third\n\n# comment 2",
                "first\n  second\n  third\n\n",
                new IndexMap([[0,12]]),
            ]
        ];
    }

    /**
     * @dataProvider rmReCases
     */
    public function test__rmRe($re, $src, $expect, $expectIm)
    {
        $im = new IndexMap([]);
        $this->assertSame($expect, Preprocessor::rmRe($re, $src, $im));
        $this->assertSame($expectIm->getArray(), $im->getArray());
        $this->assertSame($expectIm->getIncrement(), $im->getIncrement());
    }

    public function test__rmRe__twice()
    {
        //
        // Double application of Preprocessor::rmRe with same IndexMap instance.
        //
        $im = new IndexMap([]);
        //      00000000001 111111 111222222 22223333 3 33333444444 4444555555
        //      01234567890 123456 789012345 67890123 4 56789012345 6789012345
        $src = "# comment 1\nfirst\n  second\n  third\n\n# two-line\n  comment";
        $str = Preprocessor::rmRe('/\n /m', $src, $im);
        //                         00000000001 1111111112222222222 3 3333333334444444444
        //                         01234567890 1234567890123456789 0 1234567890123456789
        $this->assertSame("# comment 1\nfirst second third\n\n# two-line comment", $str);
        $this->assertSame([[0,0], [17,19], [24,28], [42,48]], $im->getArray());

        $str = Preprocessor::rmRe('/^#[^\n]*\n?/m', $str, $im);
        //                         000000000011111111 1 1
        //                         012345678901234567 8 9
        $this->assertSame("first second third\n\n", $str);
        $this->assertSame([[0,12], [5,19], [12,28], [30, 48]], $im->getArray());
    }

    public function rmLnContCases()
    {
        return [
            [ "a text\nwithout\nln cont", "a text\nwithout\nln cont", [[0,0]] ],
            [ "a text\n  with\n  ln conts", "a text with ln conts", [[0,0], [6,8], [11,15]] ],
        ];
    }

    /**
     * @dataProvider rmLnContCases
     */
    public function test__rmLnCont(string $src, string $expect, array $expectIm)
    {
        $im = new IndexMap([]);
        $this->assertSame($expect, Preprocessor::rmLnCont($src, $im));
        $this->assertSame($expectIm, $im->getArray());
        $this->assertSame(1, $im->getIncrement());
    }

    public function rmCommentsCases()
    {
        return [
            [ "A text without comments", "A text without comments", [[0,0]] ],
            [
            //   00000000001 1111111112222222222333333333344 4 444444455555 55555666666666677777777778888888
            //   01234567890 1234567890123456789012345678901 2 345678901234 56789012345678901234567890123456
                "# comment 1\ndn: cn=admin,dc=example,dc=org\n\n# comment 2\ndn: ou=people,dc=example,dc=org",
                "dn: cn=admin,dc=example,dc=org\n\ndn: ou=people,dc=example,dc=org",
            //   000000000011111111112222222222 3 33333333344444444445555555555666
            //   012345678901234567890123456789 0 12345678901234567890123456789012
                [[0,12], [32,56]]
            ],
        ];
    }

    /**
     * @dataProvider rmCommentsCases
     */
    public function test__rmComments(string $src, string $expect, array $expectIm)
    {
        $im = new IndexMap([]);
        $this->assertSame($expect, Preprocessor::rmComments($src, $im));
        $this->assertSame($expectIm, $im->getArray());
        $this->assertSame(1, $im->getIncrement());
    }

    public function preprocessCases()
    {
        $pp = new Preprocessor;

        return [
            [
                $pp,
                [""],
                [
                    "",
                    [],
                    '-'
                ]
            ],

            [
                $pp,
                ["dn: cn=admin,dc=example,dc=org\ncn: admin"],
                [
                    "dn: cn=admin,dc=example,dc=org\ncn: admin",
                    [[0,0]],
                    '-'
                ]
            ],

            [
                $pp,
                //000000000011111111112222 22222233 3333333344
                //012345678901234567890123 45678901 2345678901
                ["dn: cn=admin,dc=example,\n dc=org\ncn: admin"],
                [
                //   000000000011111111112222222222 333333333344
                //   012345678901234567890123456789 012345678901
                    "dn: cn=admin,dc=example,dc=org\ncn: admin",
                    [[0,0], [24,26]],
                    '-'
                ]
            ],

            [
                $pp,
                //00000000001 1111111112222 22222233 33333333 444444444455
                //01234567890 1234567890123 45678901 23456789 012345678901
                ["# comment 1\ndn: cn=admin,dc=example,dc=org\n# comment 2"],
                [
                //   000000000011111111112222222222 3
                //   012345678901234567890123456789 0
                    "dn: cn=admin,dc=example,dc=org\n",
                    [[0,12]],
                    '-'
                ]
            ],

            [
                $pp,
                //0000000000 111111111122 2222222233333333334444444444555
                //0123456789 012345678901 2345678901234567890123456789012
                ["version: 1\n# comment 1\ndn: cn=admin,dc=example,dc=org"],
                [
                //   0000000000 1111111111222222222233333333334 4
                //   0123456789 0123456789012345678901234567890 1
                    "version: 1\ndn: cn=admin,dc=example,dc=org",
                    [[0,0], [11,23]],
                    '-'
                ]
            ],

            [
                $pp,
                //000000000 0111 1111111222222222233333333 33444444 444455555555
                //012345678 9012 3456789012345678901234567 89012345 678901234567
                ["# comment\n  1\ndn: cn=admin,dc=example,\n dc=org\n# comment 2"],
                [
                //   000000000011111111112222222222 3
                //   012345678901234567890123456789 0
                    "dn: cn=admin,dc=example,dc=org\n",
                    [[0,14], [24,40]],
                    '-'
                ]
            ],
        ];
    }

    /**
     * @dataProvider preprocessCases
     */
    public function test__preprocess(Preprocessor $pp, array $args, array $expect)
    {
        [$expString, $expIm, $expFile] = $expect;

        $result = $pp->preprocess(...$args);

        $this->assertInstanceOf(Input::class, $result);
        $this->assertSame($args[0], $result->getSourceString());
        $this->assertSame($expString, $result->getString());

        $im = $result->getIndexMap();
        $this->assertInstanceOf(IndexMap::class, $im);
        $this->assertSame($expIm, $im->getArray());
        $this->assertSame(1, $im->getIncrement());
    }
}

// vim: syntax=php sw=4 ts=4 et:
