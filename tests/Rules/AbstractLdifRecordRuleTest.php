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

use Korowai\Lib\Ldif\Rules\AbstractLdifRecordRule;
use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Ldif\Rules\AbstractRule;
use Korowai\Lib\Ldif\Rules\DnSpecRule;
use Korowai\Lib\Ldif\Rules\SepRule;
use Korowai\Lib\Ldif\Rules\AttrValSpecRule;
use Korowai\Testing\Ldiflib\TestCase;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AbstractLdifRecordRuleTest extends TestCase
{
    public function test__extends__AbstractRule()
    {
        $this->assertExtendsClass(AbstractRule::class, AbstractLdifRecordRule::class);
    }

    public static function construct__cases()
    {
        $dnSpecRule = new DnSpecRule;
        $sepRule = new SepRule;
        $attrValSpecRule = new AttrValSpecRule;

        return [
            '__construct()' => [
                'args'   => [],
                'expect' => [
                ]
            ],
            '__construct([...])' => [
                'args'   => [[
                    'dnSpecRule' => $dnSpecRule,
                    'sepRule' => $sepRule,
                    'attrValSpecRule' => $attrValSpecRule,
                ]],
                'expect' => [
                    'dnSpecRule' => $dnSpecRule,
                    'sepRule' => $sepRule,
                    'attrValSpecRule' => $attrValSpecRule,
                ]
            ],
        ];
    }

    /**
     * @dataProvider construct__cases
     */
    public function test__construct(array $args, array $expect)
    {
        $rule = $this->getMockBuilder(AbstractLdifRecordRule::class)
                     ->setConstructorArgs($args)
                     ->getMockForAbstractClass();
        $this->assertInstanceOf(DnSpecRule::class, $rule->getDnSpecRule());
        $this->assertInstanceOf(SepRule::class, $rule->getSepRule());
        $this->assertInstanceOf(AttrValSpecRule::class, $rule->getAttrValSpecRule());
        $this->assertHasPropertiesSameAs($expect, $rule);
    }

    public function test__setDnSpecRule()
    {
        $rule = $this->getMockBuilder(AbstractLdifRecordRule::class)
                     ->getMockForAbstractClass();
        $dnSpecRule = new DnSpecRule;

        $this->assertSame($rule, $rule->setDnSpecRule($dnSpecRule));
        $this->assertSame($dnSpecRule, $rule->getDnSpecRule());
    }

    public function test__setSepRule()
    {
        $rule = $this->getMockBuilder(AbstractLdifRecordRule::class)
                     ->getMockForAbstractClass();
        $sepRule = new SepRule;

        $this->assertSame($rule, $rule->setSepRule($sepRule));
        $this->assertSame($sepRule, $rule->getSepRule());
    }

    public function test__setAttrValSpecRule()
    {
        $rule = $this->getMockBuilder(AbstractLdifRecordRule::class)
                     ->getMockForAbstractClass();
        $attrValSpecRule = new AttrValSpecRule;

        $this->assertSame($rule, $rule->setAttrValSpecRule($attrValSpecRule));
        $this->assertSame($attrValSpecRule, $rule->getAttrValSpecRule());
    }
}

// vim: syntax=php sw=4 ts=4 et:
