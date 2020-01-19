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
use Korowai\Lib\Ldif\RuleInterface;
use Korowai\Lib\Rfc\Rfc2253;

/**
 * Utility functions used by LDIF Rules.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class Util
{
    /**
     * Decodes base64-encoded string.
     *
     * @param  State $state
     * @param  string $string The string to be decoded.
     * @param  int|null $offset An offset in the input where the *$string* begins.
     *
     * @return string|null Returns the decoded data or null on error.
     */
    public static function base64Decode(State $state, string $string, int $offset) : ?string
    {
        $decoded = base64_decode($string, true);
        if ($decoded === false) {
            $state->errorAt($offset, 'syntax error: invalid BASE64 string');
            return null;
        }
        return $decoded;
    }

    /**
     * Validates string against UTF-8 encoding.
     *
     * @param  State $state
     * @param  string $string The string to be validated.
     * @param  int|null $offset An offset in the input where the *$string* begins.
     *
     * @return string|null Returns the decoded data or null on error.
     */
    public static function utf8Check(State $state, string $string, int $offset) : bool
    {
        if (mb_check_encoding($string, 'utf-8') === false) {
            $state->errorAt($offset, 'syntax error: the string is not a valid UTF8');
            return false;
        }
        return true;
    }

    /**
     * Validates *$string* against RFC2253 distinguishedName regular expression.
     *
     * @param  State $state
     * @param  string $string String containing the (decoded) distinguished name.
     * @param  int $offset Offset of the beginning of *$string* in the input.
     */
    public static function dnCheck(State $state, string $string, int $offset) : bool
    {
        if (!Scan::matchString('/\G'.Rfc2253::DISTINGUISHED_NAME.'$/D', $string)) {
            $state->errorAt($offset, 'syntax error: invalid DN syntax: "'.$string.'"');
            return false;
        }

        return true;
    }

    /**
     * Validates *$string* against RFC2253 name-component regular expression.
     *
     * @param  State $state
     * @param  string $string String containing the (decoded) name-component.
     * @param  int $offset Offset of the beginning of *$string* in the input.
     */
    public static function rdnCheck(State $state, string $string, int $offset) : bool
    {
        if (!Scan::matchString('/\G'.Rfc2253::NAME_COMPONENT.'$/D', $string)) {
            $state->errorAt($offset, 'syntax error: invalid RDN syntax: "'.$string.'"');
            return false;
        }

        return true;
    }
}
// vim: syntax=php sw=4 ts=4 et:
