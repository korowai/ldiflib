<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Tests\Lib\Ldif\Exception;

use Korowai\Testing\TestCase;
use Korowai\Lib\Ldif\Exception\InvalidRuleClassException;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class InvalidRuleClassExceptionTest extends TestCase
{
    public function test__extendsInvalidArgumentException()
    {
        $this->assertExtendsClass(\InvalidArgumentException::class, InvalidRuleClassException::class);
    }

    public static function getMessage__cases()
    {
        return [
            'default message' => [[], ''],
            'custom message'  => [['custom message'], 'custom message']
        ];
    }

    /**
     * @dataProvider getMessage__cases
     */
    public function test__getMessage(array $args, string $expect)
    {
        $e = new InvalidRuleClassException(...$args);
        $this->assertEquals($expect, $e->getMessage());
    }
}

// vim: syntax=php sw=4 ts=4 et:
