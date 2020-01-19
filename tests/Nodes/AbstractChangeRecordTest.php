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

use Korowai\Lib\Ldif\Nodes\AbstractChangeRecord;
use Korowai\Lib\Ldif\Nodes\AbstractRecord;
use Korowai\Lib\Ldif\RecordInterface;
use Korowai\Lib\Ldif\SnippetInterface;
use Korowai\Lib\Ldif\Traits\DecoratesSnippetInterface;

use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AbstractChangeRecordTest extends TestCase
{
    public function test__extends__AbstractRecord()
    {
        $this->assertExtendsClass(AbstractRecord::class, AbstractChangeRecord::class);
    }

    public function test__initAbstractChangeRecord()
    {
        $snippet = $this->getMockBuilder(SnippetInterface::class)
                        ->getMockForAbstractClass();
        $record = $this->getMockBuilder(AbstractChangeRecord::class)
                       ->getMockForAbstractClass();

        $options = ['controls' => ['Y'], 'snippet' => $snippet];
        $this->assertSame($record, $record->initAbstractChangeRecord("dc=example,dc=org", $options));
        $this->assertSame("dc=example,dc=org", $record->getDn());
        $this->assertSame(['Y'], $record->getControls());
        $this->assertSame($snippet, $record->getSnippet());
    }

    public function test__setControls()
    {
        $record = $this->getMockBuilder(AbstractChangeRecord::class)
                       ->getMockForAbstractClass();

        $this->assertSame($record, $record->setControls(['Y']));
        $this->assertSame(['Y'], $record->getControls());
    }
}

// vim: syntax=php sw=4 ts=4 et:
