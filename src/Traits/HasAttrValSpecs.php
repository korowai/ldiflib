<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif\Traits;

use Korowai\Lib\Ldif\RecordVisitorInterface;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait HasAttrValSpecs
{
    /**
     * @var array
     */
    protected $attrValSpecs;

    /**
     * Sets the new array of attribute value specifications to this record.
     *
     * @param  array $attrValSpecs
     *
     * @return object $this
     */
    public function setAttrValSpecs(array $attrValSpecs)
    {
        $this->attrValSpecs = $attrValSpecs;
        return $this;
    }

    /**
     * Returns array of [AttrValInterface](AttrValInterface.html) objects that
     * comprise the record.
     *
     * @return array an array of [AttrValInterface](AttrValInterface.html) instances.
     */
    public function getAttrValSpecs() : array
    {
        return $this->attrValSpecs;
    }
}

// vim: syntax=php sw=4 ts=4 et:
