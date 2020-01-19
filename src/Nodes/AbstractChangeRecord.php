<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif\Nodes;

use Korowai\Lib\Ldif\SnippetInterface;

/**
 * An abstract base class for parsed LDIF records.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractChangeRecord extends AbstractRecord
{
    /**
     * @var array
     */
    private $controls;

    /**
     * Initializes the object. Should be invoked from subclass' constructor.
     *
     * @param  string $dn
     *      Distinguished name of the entry being altered by the record.
     * @param  array $options
     *      An array of key => value pairs. Supported options are:
     *
     * - ``"controls" => ControlInterface[]`` (optional): an optional
     *   array of controls for the operation.
     * - ``"snippet" => SnippetInterface`` (optional): an optional
     *   instance of [SnippetInterface](\.\./SnippetInterface.html) to be
     *   attached to this record.
     *
     * Unsupported keys are silently ignored.
     *
     * @return object $this
     */
    public function initAbstractChangeRecord(string $dn, array $options = [])
    {
        $this->initAbstractRecord($dn, $options);
        $this->setControls($options['controls'] ?? []);
        return $this;
    }

    /**
     * Sets new controls to this object.
     *
     * @param  array $controls
     *
     * @return object $this
     */
    public function setControls(array $controls)
    {
        $this->controls = $controls;
        return $this;
    }

    /**
     * Returns the controls assigned to this object.
     *
     * @return array
     */
    public function getControls() : array
    {
        return $this->controls;
    }
}

// vim: syntax=php sw=4 ts=4 et:
