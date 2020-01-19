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
 * @todo Write documentation.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractLdifRecordRule extends AbstractRule
{
    /**
     * @var DnSpecRule
     */
    private $dnSpecRule;

    /**
     * @var SepRule
     */
    private $sepRule;

    /**
     * @var attrValSpecRule
     */
    private $attrValSpecRule;

    /**
     * Initializes the object.
     *
     * @param  array $options
     * @return object $this
     */
    public function __construct(array $options = [])
    {
        $this->setDnSpecRule($options['dnSpecRule'] ?? new DnSpecRule);
        $this->setSepRule($options['sepRule'] ?? new SepRule);
        $this->setAttrValSpecRule($options['attrValSpecRule'] ?? new AttrValSpecRule);
        return $this;
    }

    /**
     * Returns the nested DnSpecRule object.
     *
     * @return DnSpecRule
     */
    public function getDnSpecRule() : ?DnSpecRule
    {
        return $this->dnSpecRule;
    }

    /**
     * Sets new nested DnSpecRule object.
     *
     * @param  DnSpecRule $rule
     * @return object $this
     */
    public function setDnSpecRule(DnSpecRule $rule)
    {
        $this->dnSpecRule = $rule;
        return $this;
    }

    /**
     * Returns the nested SepRule object.
     *
     * @return SepRule
     */
    public function getSepRule() : ?SepRule
    {
        return $this->sepRule;
    }

    /**
     * Sets new nested SepRule object.
     *
     * @param  SepRule $rule
     * @return object $this
     */
    public function setSepRule(SepRule $rule)
    {
        $this->sepRule = $rule;
        return $this;
    }

    /**
     * Returns the nested AttrValSpecRule object.
     *
     * @return AttrValSpecRule
     */
    public function getAttrValSpecRule() : ?AttrValSpecRule
    {
        return $this->attrValSpecRule;
    }

    /**
     * Sets new nested AttrValSpecRule object.
     *
     * @param  AttrValSpecRule $rule
     * @return object $this
     */
    public function setAttrValSpecRule(AttrValSpecRule $rule)
    {
        $this->attrValSpecRule = $rule;
        return $this;
    }
}
// vim: syntax=php sw=4 ts=4 et:
