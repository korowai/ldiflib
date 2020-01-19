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

/**
 * A rule object that implements *dn-spec* rule defined in RFC2849.
 *
 * - semantic value: string
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractNameSpecRule extends AbstractRfcRule
{
    /**
     * Returns prefix which determines what kind of names we're parsing. Should
     * return ``"dn"`` for distringuished names and ``"rdn"`` for relative
     * distinguished names.
     *
     * @return string
     */
    abstract public function prefix() : string;

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
        if (Scan::matched('value_b64', $matches, $string, $offset)) {
            $value = Util::base64Decode($state, $string, $offset);
            if (!$this->utf8Check($state, $value, $offset)) {
                return false;
            }
            return $this->validate($state, $value, $offset);
        } elseif (Scan::matched('value_safe', $matches, $value, $offset)) {
            return $this->validate($state, $value, $offset);
        }

        // This may happen with broken Rfc rule.
        $value = null;
        $state->errorHere('internal error: missing or invalid capture groups "value_safe" and "value_b64"');
        return false;
    }

    /**
     * Checks UTF8 string using Util::utf8Check().
     *
     * @param  State $state
     * @param  string $value
     * @param  int $offset
     *
     * @return bool
     */
    protected function utf8Check(State $state, ?string &$value, int $offset)
    {
        return static::checkWith([Util::class, 'utf8Check'], $state, $value, $offset);
    }

    /**
     * Checks the captured string with $this->validator().
     *
     * @param  State $state
     * @param  string $value
     * @param  int $offset
     *
     * @return bool
     */
    protected function validate(State $state, string &$value, int $offset) : bool
    {
        $func = [Util::class, $this->prefix().'Check'];
        return static::checkWith($func, $state, $value, $offset);
    }

    /**
     * Delegates value checking to *$func*.
     *
     * @param  callable $func
     * @param  State $state
     * @param  string $value
     * @param  int $offset
     *
     * @return bool
     */
    protected function checkWith(callable $func, State $state, ?string &$value, int $offset)
    {
        if ($value === null) {
            return  false;
        }
        if (!call_user_func_array($func, [$state, $value, $offset])) {
            $value = null;
            return false;
        }
        return true;
    }
}

// vim: syntax=php sw=4 ts=4 et:
