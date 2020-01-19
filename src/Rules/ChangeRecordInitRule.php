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
use Korowai\Lib\Ldif\ChangeRecord;
use Korowai\Lib\Ldif\Exception\InvalidModTypeException;
use Korowai\Lib\Rfc\Rfc2849;

/**
 * A rule object that implements ``CHANGERECORD_INIT`` rule defined in
 * [Rfc2849](\.\./\.\./Rfc/Rfc2849.html).
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class ChangeRecordInitRule extends AbstractRfcRule
{
    /**
     * Initializes the object.
     */
    public function __construct()
    {
        parent::__construct(Rfc2849::class, 'CHANGERECORD_INIT');
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
        if (Scan::matched('chg_type', $matches, $value, $offset)) {
            return $this->checkChangeType($state, $value, $offset);
        }

        $message = 'internal error: missing or invalid capture group "chg_type"';
        $state->errorHere($message);
        $value = null;
        return false;
    }

    /**
     * Check that *$value* is one of the supported change type strings.
     *
     * @param  State $state
     * @param  string $value
     * @param  int $offset
     * @return bool
     */
    protected function checkChangeType(State $state, string &$value, int $offset)
    {
        if (!in_array($value, ['add', 'delete', 'moddn', 'modrdn', 'modify'])) {
            $message = 'syntax error: unsupported change type: "'.$value.'"';
            $state->errorAt($offset, $message);
            $value = null;
            return false;
        }
        return true;
    }
}
// vim: syntax=php sw=4 ts=4 et:
