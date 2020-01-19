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
use Korowai\Lib\Ldif\ValueInterface;
use Korowai\Lib\Ldif\Value;
use Korowai\Lib\Rfc\Rfc2849;
use League\Uri\Exceptions\SyntaxError as UriSyntaxError;

/**
 * A rule that parses RFC2849 value-spec.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class ValueSpecRule extends AbstractRfcRule
{
    /**
     * Initializes the object.
     */
    public function __construct()
    {
        parent::__construct(Rfc2849::class, 'VALUE_SPEC');
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
        if (Scan::matched('value_safe', $matches, $string, $offset)) {
            $value = Value::createSafeString($string);
            return true;
        } elseif (Scan::matched('value_b64', $matches, $string, $offset)) {
            return $this->parseMatchedBase64String($state, $string, $offset, $value);
        } elseif (Scan::matched('value_url', $matches, $string, $offset)) {
            return $this->parseMatchedUriReference($state, $matches, $value);
        }

        $message = 'internal error: missing or invalid capture groups "value_safe", "value_b64" and "value_url"';
        $state->errorHere($message);
        $value = null;
        return false;
    }

    /**
     *
     * @param  State $state
     * @param  string $string
     * @param  int $offset
     * @param  ValueInterface $value
     *
     * @return bool
     */
    protected function parseMatchedBase64String(
        State $state,
        string $string,
        int $offset,
        ValueInterface &$value = null
    ) : bool {
        $decoded = Util::base64Decode($state, $string, $offset);
        if (null === $decoded) {
            $value = null;
            return false;
        }
        $value = Value::createBase64String($string, $decoded);
        return true;
    }

    /**
     * Make URI reference
     *
     * @param  State $state
     * @param  array $matches
     * @param  array $value
     *
     * @return bool
     */
    protected function parseMatchedUriReference(
        State $state,
        array $matches,
        ValueInterface &$value = null
    ) : bool {
        try {
            $value = Value::createUriFromRfc3986Matches($matches);
        } catch (UriSyntaxError $e) {
            $state->errorHere('syntax error: in URL: '.$e->getMessage());
            $value = null;
            return false;
        }
        return true;
    }
}
// vim: syntax=php sw=4 ts=4 et:
