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
use Korowai\Lib\Ldif\Control;
use Korowai\Lib\Rfc\Rfc2849;

/**
 * @todo Write documentation.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class ControlRule extends AbstractRfcRule
{
    /**
     * Initializes the object.
     *
     * @param  array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct(Rfc2849::class, 'CONTROL');
        $this->setValueSpecRule($options['valueSpecRule'] ?? new ValueSpecRule);
    }

    /**
     * Returns the internal instance of [ValueSpecRule](ValueSpecRule.html).
     *
     * @return ValueSpecRule|null
     */
    public function getValueSpecRule() : ?ValueSpecRule
    {
        return $this->valueSpecRule;
    }

    /**
     * Sets the new instance of ValueSpecRule to this object.
     *
     * @param  ValueSpecRule $valueSpecRule
     *
     * @return object $this
     */
    public function setValueSpecRule(?ValueSpecRule $valueSpecRule)
    {
        $this->valueSpecRule = $valueSpecRule;
        return $this;
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
        if (Scan::matched('ctl_type', $matches, $ctl_type, $offset)) {
            if (!$this->parseCriticalityIfMatched($state, $matches, $ctl_crit) ||
                !$this->parseValueSpecIfMatched($state, $matches, $ctl_value)) {
                $value = null;
                return false;
            }
            $value = new Control($ctl_type, $ctl_crit, $ctl_value);
            return true;
        }
        $value = null;
        $state->errorHere('internal error: missing or invalid capture group "ctl_type"');
        return false;
    }

    /**
     * @param  State $state
     * @param  array $matches
     * @param  bool $criticality
     * @return bool
     */
    protected function parseCriticalityIfMatched(State $state, array $matches, bool &$criticality = null)
    {
        if (!Scan::matched('ctl_crit', $matches, $ctl_crit, $offset)) {
            $criticality = null;
            return true;
        }

        switch (strtolower($ctl_crit)) {
            case 'true':
                $criticality = true;
                break;
            case 'false':
                $criticality = false;
                break;
            default:
                $criticality = null;
                $state->errorAt($offset, 'syntax error: invalid control criticality: "'.$ctl_crit.'"');
                return false;
        }
        return true;
    }

    /**
     * @param  State $state
     * @param  array $matches
     * @param  bool $value
     * @return bool
     */
    protected function parseValueSpecIfMatched(State $state, array $matches, ValueInterface &$value = null)
    {
        if (!Scan::matched('value_safe', $matches) &&
            !Scan::matched('value_b64', $matches) &&
            !Scan::matched('value_url', $matches)) {
            $value = null;
            return true;
        }
        return $this->getValueSpecRule()->parseMatched($state, $matches, $value);
    }
}
// vim: syntax=php sw=4 ts=4 et:
