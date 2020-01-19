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

use Korowai\Lib\Ldif\Nodes\ModDnRecord;
use Korowai\Lib\Ldif\Nodes\ModDnRecordInterface;
use Korowai\Lib\Ldif\Nodes\AbstractRecord;
use Korowai\Lib\Ldif\RecordVisitorInterface;
use Korowai\Lib\Ldif\SnippetInterface;
use Korowai\Lib\Ldif\Exception\InvalidChangeTypeException;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModDnRecordTest extends TestCase
{
    public function tets__extends__AbstractRecord()
    {
        $this->assertExtendsClass(AbstractRecord::class, AttraValRecord::class);
    }

    public function test__implements__ModDnRecordInterface()
    {
        $this->assertImplementsInterface(ModDnRecordInterface::class, ModDnRecord::class);
    }

    public function construct__cases()
    {
        $snippet = $this->getMockBuilder(SnippetInterface::class)->getMockForAbstractClass();
        return [
            '__construct("dc=example,dc=org", "cn=bar")' => [
                'args' => [
                    'dc=example,dc=org',
                    'cn=bar',
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'newRdn' => 'cn=bar',
                    'changeType' => 'modrdn',
                    'deleteOldRdn' => false,
                    'newSuperior' => null,
                    'controls' => [],
                    'snippet' => null,
                ]
            ],
            '__construct("dc=example,dc=org", "cn=bar", [...])' => [
                'args' => [
                    'dc=example,dc=org',
                    'cn=bar',
                    [
                        'changetype' => 'moddn',
                        'deleteoldrdn' => true,
                        'newsuperior' => 'dc=foobar,dc=com',
                        'controls' => ['Y'],
                        'snippet' => $snippet,
                    ],
                ],
                'expect' => [
                    'dn' => 'dc=example,dc=org',
                    'newRdn' => 'cn=bar',
                    'changeType' => 'moddn',
                    'deleteOldRdn' => true,
                    'newSuperior' => 'dc=foobar,dc=com',
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
        $record = new ModDnRecord(...$args);
        $this->assertHasPropertiesSameAs($expect, $record);
    }

    public static function setChangeType__cases()
    {
        return [
            ["moddn"],
            ["modrdn"]
        ];
    }

    /**
     * @dataProvider setChangeType__cases
     */
    public function test__setChangeType(string $changeType)
    {
        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $this->assertSame($record, $record->setChangeType($changeType));
        $this->assertSame($changeType, $record->getChangeType());
    }

    public function test__setChangeType__invalidChangeType()
    {
        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $message = 'Argument 1 to '.ModDnRecord::class.'::setChangeType() must be one of "moddn" or "modrdn", '.
                   '"foo" given.';
        $this->expectException(InvalidChangeTypeException::class);
        $this->expectExceptionMessage($message);

        $record->setChangeType("foo");
    }

    public function test__setNewRdn()
    {
        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $this->assertSame($record, $record->setNewRdn("cn=gez"));
        $this->assertSame("cn=gez", $record->getNewRdn());
    }

    public function test__setDeleteOldRdn()
    {
        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $this->assertSame($record, $record->setDeleteOldRdn(true));
        $this->assertSame(true, $record->getDeleteOldRdn());

        $this->assertSame($record, $record->setDeleteOldRdn(false));
        $this->assertSame(false, $record->getDeleteOldRdn());
    }

    public function test__setNewSuperior()
    {
        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $this->assertSame($record, $record->setNewSuperior("dc=foobar,dc=com"));
        $this->assertSame("dc=foobar,dc=com", $record->getNewSuperior());

        $this->assertSame($record, $record->setNewSuperior(null));
        $this->assertSame(null, $record->getNewSuperior());
    }

    public function test__acceptRecordVisitor()
    {
        $visitor = $this->getMockBuilder(RecordVisitorInterface::class)
                        ->getMockForAbstractClass();

        $record = new ModDnRecord("dc=example,dc=org", "cn=bar");

        $visitor->expects($this->once())
                ->method('visitModDnRecord')
                ->with($record)
                ->will($this->returnValue('ok'));

        $this->assertSame('ok', $record->acceptRecordVisitor($visitor));
    }
}

// vim: syntax=php sw=4 ts=4 et:
