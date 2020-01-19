<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif\Rules;

use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Ldif\ParserStateInterface as State;

/**
 * Abstract base class for LDIF rules.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractRule implements RuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function repeat(State $state, array &$values = null, int $min = 0, int $max = null) : bool
    {
        $errors = count($state->getErrors());
        $values = [];
        for ($count = 0; $count < ($max ?? PHP_INT_MAX); $count++) {
            if (!$this->parse($state, $value, $count >= $min)) {
                return !(count($state->getErrors()) > $errors);
            }
            $values[] = $value;
        }
        return true;
    }
}
// vim: syntax=php sw=4 ts=4 et:
