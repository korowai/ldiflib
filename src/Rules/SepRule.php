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

use Korowai\Lib\Ldif\ParserStateInterface as State;
use Korowai\Lib\Ldif\Scan;
use Korowai\Lib\Rfc\Rfc2849;

/**
 * A rule that parses single line separator RFC2849.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class SepRule extends AbstractRfcRule
{
    /**
     * Initializes the object.
     */
    public function __construct()
    {
        parent::__construct(Rfc2849::class, 'SEP');
    }

    /**
     * Completes parsing with rule by validating substrings captured by the
     * rule (*$matches*) and forming semantic value out of *$matches*.
     *
     * The purpose of the *parseMatched()* method is to validate the captured
     * values (passed in via *$matches*) and optionally produce and return
     * to the caller any semantic *$value*. The function shall return true on
     * success or false on failure.
     *
     * @param  State $state
     *      Provides the input string, cursor, containers for errors, etc..
     * @param  array $matches
     *      An array of matches as returned from *preg_match()*. Contains
     *      substrings captured by the encapsulated RFC rule.
     * @param  mixed $value
     *      Semantic value to be returned to caller.
     * @return bool true on success, false on failure.
     */
    public function parseMatched(State $state, array $matches, &$value = null) : bool
    {
        if (Scan::matched(0, $matches, $value, $offset)) {
            return true;
        }
        $value = null;
        $state->errorHere('internal error: missing or invalid capture group 0');
        return false;
    }
}
// vim: syntax=php sw=4 ts=4 et:
