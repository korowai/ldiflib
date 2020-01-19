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
use Korowai\Lib\Ldif\Scan;
use Korowai\Lib\Rfc\Traits\DecoratesRuleInterface;
use Korowai\Lib\Rfc\Rule;

/**
 * Base class for LDIF parsing rules that decorate RFC
 * [RuleInterface](\.\./\.\./Rfc/RuleInterface.html).
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractRfcRule extends AbstractRule implements \Korowai\Lib\Rfc\RuleInterface
{
    use DecoratesRuleInterface;

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
     */
    abstract public function parseMatched(State $state, array $matches, &$value = null) : bool;

    /**
     * Initializes the object.
     *
     * @param  string $rfcRuleSet
     *      Name of the rfc ruleset class.
     * @param  string $rfcRuleId
     *      Name of the rule in the ruleset class.
     */
    public function __construct(string $rfcRuleSet, string $rfcRuleId)
    {
        $this->setRfcRule(new Rule($rfcRuleSet, $rfcRuleId));
    }

    /**
     * {@inheritdoc}
     */
    public function parse(State $state, &$value = null, bool $trying = false) : bool
    {
        if (!$this->match($state, $matches, $trying)) {
            $value = null;
            return false;
        }
        return $this->parseMatched($state, $matches, $value);
    }

    /**
     * Matches the input substring starting at *$state*'s cursor against
     * regular expression provided by *$rule* and moves the cursor after
     * the end of the matched substring.
     *
     * @param  State $state
     *      The state provides cursor pointing to the offset of the beginning
     *      of the match. If the *$rule* matches anything, the *$state*'s
     *      cursor gets moved to the character next after the matched string.
     *      If *$rule* matches any errors, they will be appended to *$state*.
     * @param  array $matches
     *      Returns matched captured groups including matched errors. If the
     *      rule doesn't match at all, the function returns empty *$matches*.
     * @param  bool $trying
     *      If ``false``, error is appended to *$state* when the rule does not match.
     *
     * @return bool
     *      Returns false if rule doesn't match, or if the returned *$matches*
     *      include errors.
     */
    public function match(State $state, array &$matches = null, bool $trying = false) : bool
    {
        $cursor = $state->getCursor();

        $matches = Scan::matchAhead('/\G'.$this.'/D', $cursor, PREG_UNMATCHED_AS_NULL);
        if (empty($matches)) {
            if (!$trying) {
                $message = $this->getErrorMessage();
                $state->errorHere('syntax error: '.$message);
            }
            return false;
        }

        $errors = $this->findCapturedErrors($matches);
        foreach ($errors as $errorKey => $errorMatch) {
            $message = $this->getErrorMessage($errorKey);
            $state->errorAt($errorMatch[1], 'syntax error: '.$message);
        }

        return empty($errors);
    }
}
// vim: syntax=php sw=4 ts=4 et:
