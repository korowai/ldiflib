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
use Korowai\Lib\Ldif\ModSpec;
use Korowai\Lib\Rfc\Rfc2849;

/**
 * @todo Write documentation.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class ModSpecRule extends AbstractRule
{

    /**
     * @var ModSpecInitRule
     */
    public $modSpecInitRule;

    /**
     * @var AttrValSpecRule
     */
    public $attrValSpecRule;

    /**
     * Initializes the object.
     *
     * @param  array $options
     */
    public function __construct(array $options = [])
    {
        $this->setModSpecInitRule($options['modSpecInitRule'] ?? new ModSpecInitRule());
        $this->setAttrValSpecRule($options['attrValSpecRule'] ?? new AttrValSpecRule());
    }

    /**
     * Sets new instance of [ModSpecInitRule](ModSpecInitRule.html).
     *
     * @param  ModSpecInitRule $modSpecInitRule
     * @return object $this
     */
    public function setModSpecInitRule(ModSpecInitRule $modSpecInitRule)
    {
        $this->modSpecInitRule = $modSpecInitRule;
        return $this;
    }

    /**
     * Returns the instance of [ModSpecInitRule](ModSpecInitRule.html)
     *
     * @return ModSpecInitRule
     */
    public function getModSpecInitRule() : ModSpecInitRule
    {
        return $this->modSpecInitRule;
    }

    /**
     * Sets new instance of [AttrValSpecRule](AttrValSpecRule.html).
     *
     * @param  AttrValSpecRule $attrValSpecRule
     * @return object $this
     */
    public function setAttrValSpecRule(AttrValSpecRule $attrValSpecRule)
    {
        $this->attrValSpecRule = $attrValSpecRule;
        return $this;
    }

    /**
     * Returns the instance of [AttrValSpecRule](AttrValSpecRule.html).
     *
     * @return AttrValSpecRule
     */
    public function getAttrValSpecRule() : AttrValSpecRule
    {
        return $this->attrValSpecRule;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(State $state, &$value = null, bool $trying = false) : bool
    {
        if (!$this->getModSpecInitRule()->parse($state, $value, $trying) ||
            !$this->getAttrValSpecRule()->repeat($state, $attrVals)) {
            return false;
        }
        $value->setAttrValSpecs($attrVals);
        return $this->parseEndMarker($state);
    }

    /**
     * Ensures that end marker "-\n" at the current location.
     *
     * @param  State $state
     * @retrun bool
     */
    protected function parseEndMarker(State $state) : bool
    {
        $cursor = $state->getCursor();
        if (!Scan::matchAhead('/\G-'.Rfc2849::EOL.'/D', $cursor, PREG_UNMATCHED_AS_NULL)) {
            $state->errorHere('syntax error: expected "-" followed by end of line');
            return false;
        }
        return true;
    }
}
// vim: syntax=php sw=4 ts=4 et:
