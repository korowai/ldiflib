<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Testing\Lib\Ldif;

use Korowai\Testing\TestCase as BaseTestCase;
use Korowai\Testing\Ldiflib\TestCase;
use Korowai\Testing\Ldiflib\Traits\ParserTestHelpers;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class TestCaseTest extends TestCase
{
    public function test__extends__TestCase()
    {
        $this->assertExtendsClass(BaseTestCase::class, parent::class);
    }

    public function test__uses__ParserTestHelpers()
    {
        $this->assertUsesTrait(ParserTestHelpers::class, parent::class);
    }

    public function test__objectPropertyGettersMap()
    {
        $expected = array_merge_recursive(
            \Korowai\Testing\Contracts\ObjectPropertyGettersMap::getObjectPropertyGettersMap(),
            \Korowai\Testing\Rfclib\ObjectPropertyGettersMap::getObjectPropertyGettersMap(),
            \Korowai\Testing\Ldiflib\ObjectPropertyGettersMap::getObjectPropertyGettersMap()
        );
        $this->assertSame($expected, parent::objectPropertyGettersMap());
    }
}

// vim: syntax=php sw=4 ts=4 et:
