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
use Korowai\Lib\Ldif\AttrVal;
use Korowai\Lib\Rfc\Rfc2849;

/**
 * A rule object that implements *value-spec* rule defined in RFC2849.
 *
 * - semantic value: [AttrVal](\.\./AttrVal.html).
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class AttrValSpecRule extends AbstractRfcRule
{
    /**
     * @var Rule
     */
    protected $valueSpecRule;

    /**
     * Initializes the object.
     *
     * @param  ValueSpecRule $valueSpecRule
     *      Optional instance of [ValueSpecRule](ValueSpecRule.html), if not provided,
     *      the instance is created internally.
     */
    public function __construct(ValueSpecRule $valueSpecRule = null)
    {
        parent::__construct(Rfc2849::class, 'ATTRVAL_SPEC');
        if ($valueSpecRule === null) {
            $valueSpecRule = new ValueSpecRule;
        }
        $this->setValueSpecRule($valueSpecRule);
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
        if (Scan::matched('attr_desc', $matches, $string, $offset)) {
            if (!$this->valueSpecRule->parseMatched($state, $matches, $tmp)) {
                $value = null;
                return false;
            }
            $value = new AttrVal($string, $tmp);
            return true;
        }

        // This may happen with broken Rfc2849::ATTRVAL_SPEC rule.
        $state->errorHere('internal error: missing or invalid capture group "attr_desc"');
        $value = null;
        return false;
    }
}
// vim: syntax=php sw=4 ts=4 et:
