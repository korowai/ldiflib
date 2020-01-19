<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Lib\Ldif\Nodes;

use Korowai\Lib\Ldif\Nodes\DeleteRecord;
use Korowai\Lib\Ldif\Nodes\DeleteRecordInterface;
use Korowai\Lib\Ldif\Nodes\AbstractChangeRecord;
use Korowai\Lib\Ldif\RecordVisitorInterface;
use Korowai\Lib\Ldif\SnippetInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class DeleteRecordTest extends TestCase
{
    public function tets__extends__AbstractChangeRecord()
    {
        $this->assertExtendsClass(AbstractChangeRecord::class, AttraValRecord::class);
    }

    public function test__implements__DeleteRecordInterface()
    {
        $this->assertImplementsInterface(DeleteRecordInterface::class, DeleteRecord::class);
    }

    public function construct__cases()
    {
        $snippet = $this->getMockBuilder(SnippetInterface::class)->getMockForAbstractClass();

        return [
            '__construct("dc=example,dc=org")' => [
                'args' => [
                    'dc=example,dc=org',
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'changeType' => 'delete',
                    'controls' => [],
                    'snippet' => null,
                ]
            ],
            '__construct("dc=example,dc=org", ["controls" => ["Y"], "snippet" => $snippet]' => [
                'args' => [
                    'dc=example,dc=org',
                    [
                        "controls" => ['Y'],
                        "snippet" => $snippet,
                    ],
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'changeType' => 'delete',
                    'controls' => ['Y'],
                    'snippet' => $snippet
                ]
            ]
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $record = new DeleteRecord(...$args);
        $this->assertHasPropertiesSameAs($expect, $record);
    }

    public function test__acceptRecordVisitor()
    {
        $visitor = $this->getMockBuilder(RecordVisitorInterface::class)
                        ->getMockForAbstractClass();

        $record = new DeleteRecord("dc=example,dc=org");

        $visitor->expects($this->once())
                ->method('visitDeleteRecord')
                ->with($record)
                ->will($this->returnValue('ok'));

        $this->assertSame('ok', $record->acceptRecordVisitor($visitor));
    }
}

// vim: syntax=php sw=4 ts=4 et:
