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

use Korowai\Lib\Ldif\Nodes\ModifyRecord;
use Korowai\Lib\Ldif\Nodes\ModifyRecordInterface;
use Korowai\Lib\Ldif\Nodes\AbstractChangeRecord;
use Korowai\Lib\Ldif\RecordVisitorInterface;
use Korowai\Lib\Ldif\SnippetInterface;
use Korowai\Lib\Ldif\Exception\InvalidChangeTypeException;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModifyRecordTest extends TestCase
{
    public function tets__extends__AbstractChangeRecord()
    {
        $this->assertExtendsClass(AbstractChangeRecord::class, AttraValRecord::class);
    }

    public function test__implements__ModifyRecordInterface()
    {
        $this->assertImplementsInterface(ModifyRecordInterface::class, ModifyRecord::class);
    }

    public function construct__cases()
    {
        $snippet = $this->getMockBuilder(SnippetInterface::class)->getMockForAbstractClass();
        return [
            '__construct(, "dc=example,dc=org")' => [
                'args' => [
                    'dc=example,dc=org',
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'changeType' => 'modify',
                    'modSpecs' => [],
                    'controls' => [],
                    'snippet' => null,
                ]
            ],
            '__construct("dc=example,dc=org", ["modSpecs" => ["X"], "controls" => ["Y"], "snippet" => $snippet]' => [
                'args' => [
                    'dc=example,dc=org',
                    [
                        "modSpecs" => ['X'],
                        "controls" => ['Y'],
                        "snippet" => $snippet
                    ],
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'changeType' => 'modify',
                    'modSpecs' => ['X'],
                    'controls' => ['Y'],
                    'snippet' => $snippet,
                ]
            ]
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $record = new ModifyRecord(...$args);
        $this->assertHasPropertiesSameAs($expect, $record);
    }

    public function test__getChangeType()
    {
        $record = new ModifyRecord("dc=example,dc=org");
        $this->assertSame("modify", $record->getChangeType());
    }

    public function test__setModSpecs()
    {
        $record = new ModifyRecord("dc=example,dc=org");

        $this->assertSame($record, $record->setModSpecs(['X']));
        $this->assertSame(['X'], $record->getModSpecs());
    }

    public function test__acceptRecordVisitor()
    {
        $visitor = $this->getMockBuilder(RecordVisitorInterface::class)
                        ->getMockForAbstractClass();

        $record = new ModifyRecord("dc=example,dc=org", ['X']);

        $visitor->expects($this->once())
                ->method('visitModifyRecord')
                ->with($record)
                ->will($this->returnValue('ok'));

        $this->assertSame('ok', $record->acceptRecordVisitor($visitor));
    }
}

// vim: syntax=php sw=4 ts=4 et:
