<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Testing\Lib\Ldif\Traits;

use Korowai\Testing\TestCase;
use Korowai\Testing\Ldiflib\Traits\ParserTestHelpers;
use Korowai\Lib\Ldif\Input;
use Korowai\Lib\Ldif\Cursor;
use Korowai\Lib\Ldif\ParserState;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ParserTestHelpersTest extends TestCase
{
    use ParserTestHelpers;

    public static function inputFromSource__cases()
    {
        return [
            [
                ["# comment\nversion: 1\n"],
                [
                    'string' => "version: 1\n",
                    'file' => '-',
                ]
            ],
            [
                ["# comment\nversion: 1\n", ['filename' => 'foo.ldif']],
                [
                    'string' => "version: 1\n",
                    'file' => 'foo.ldif',
                ]
            ],
        ];
    }

    public static function getInputFromSource__cases()
    {
        return static::inputFromSource__cases();
    }

    /**
     * @dataProvider getInputFromSource__cases
     */
    public function test__getInputFromSource(array $args, array $expectations)
    {
        $input = $this->getInputFromSource(...$args);
        $this->assertInstanceOf(Input::class, $input);
        $this->assertSame($expectations['string'], $input->getString());
        $this->assertSame($args[0], $input->getSourceString());
        $this->assertSame($expectations['file'], $input->getSourceFileName());
    }

    public static function getCursorFromSource__cases()
    {
        $cases = [
            [
                ["#commend\nversion: 1\n", 4],
                [
                    'string' => "version: 1\n",
                    'file' => '-',
                    'offset' => 4
                ]
            ],
            [
                ["#commend\nversion: 1\n", 4, ['filename' => 'foo.ldif']],
                [
                    'string' => "version: 1\n",
                    'file' => 'foo.ldif',
                    'offset' => 4
                ]
            ]
        ];

        $inheritedCases = array_map(function ($case) {
            $args = $case[0];
            $expectations = $case[1];
            return [
                ((count($args) > 1) ? [$args[0], 0, $args[1]] : $args),
                array_merge($expectations, ['offset' => 0])
            ];
        }, static::inputFromSource__cases());

        return array_merge($inheritedCases, $cases);
    }

    /**
     * @dataProvider getCursorFromSource__cases
     */
    public function test__getCursorFromSource(array $args, array $expectations)
    {
        $cursor = $this->getCursorFromSource(...$args);
        $this->assertInstanceOf(Cursor::class, $cursor);
        $this->assertSame($expectations['string'], $cursor->getString());
        $this->assertSame($args[0], $cursor->getSourceString());
        $this->assertSame($expectations['file'], $cursor->getSourceFileName());
        $this->assertSame($expectations['offset'], $cursor->getOffset());
    }

    public static function getParserStateFromSource__cases()
    {
        $cases = [
            [
                ["#commend\nversion: 1\n", 4, ['errors' => ['E'], 'records' => ['R']]],
                [
                    'string' => "version: 1\n",
                    'file' => '-',
                    'offset' => 4,
                    'errors' => ['E'],
                    'records' => ['R'],
                ]
            ],
            [
                ["#commend\nversion: 1\n", 4, ['filename' => 'foo.ldif', 'errors' => ['E'], 'records' => ['R']]],
                [
                    'string' => "version: 1\n",
                    'file' => 'foo.ldif',
                    'offset' => 4,
                    'errors' => ['E'],
                    'records' => ['R'],
                ]
            ]
        ];
        $inheritedCases = array_map(function (array $case) {
            $args = $case[0];
            $expectations = $case[1];
            return [
                $args,
                array_merge($expectations, ['errors' => [], 'records' => []])
            ];
        }, static::getCursorFromSource__cases());

        return array_merge($inheritedCases, $cases);
    }

    /**
     * @dataProvider getParserStateFromSource__cases
     */
    public function test__getParserStateFromSource(array $args, array $expectations)
    {
        $state = $this->getParserStateFromSource(...$args);
        $this->assertInstanceOf(ParserState::class, $state);
        $this->assertSame($expectations['string'], $state->getCursor()->getString());
        $this->assertSame($args[0], $state->getCursor()->getSourceString());
        $this->assertSame($expectations['file'], $state->getCursor()->getSourceFileName());
        $this->assertSame($expectations['offset'], $state->getCursor()->getOffset());
        $this->assertSame($expectations['errors'], $state->getErrors());
        $this->assertSame($expectations['records'], $state->getRecords());
    }
}

// vim: syntax=php sw=4 ts=4 et:
