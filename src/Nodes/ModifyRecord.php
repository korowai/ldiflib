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
use Korowai\Lib\Ldif\RecordVisitorInterface;
use Korowai\Lib\Ldif\Exception\InvalidChangeTypeException;

/**
 * Represents [RFC2849](https://tools.ietf.org/html/rfc2849)
 * *ldif-change-record* of type *change-modify*.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModifyRecord extends AbstractChangeRecord implements ModifyRecordInterface
{
    /**
     * @var string
     */
    private $attribute;

    /**
     * @var array
     */
    private $modSpecs;

    /**
     * Initializes the object.
     *
     * @param  string $dn
     *      Distinguished name of the entry being altered by the record.
     * @param  array $options
     *      An array of key => value pairs. Supported options are:
     *
     * - ``"modSpecs" => ModSpecInterface[]`` (optional): an array of [ModSpecInterface](\.\./ModSpecInterface.html)
     *   instances describing modifications to be applied to the entry,
     * - ``"controls" => ControlInterface[]`` (optional): an array of controls for the operation,
     * - ``"snippet" => SnippetInterface`` (optional): an optional instance of
     *   [SnippetInterface](\.\./SnippetInterface.html) to be attached to this record.
     *
     * Unsupported keys are silently ignored.
     */
    public function __construct(string $dn, array $options = [])
    {
        parent::initAbstractChangeRecord($dn, $options);
        $this->setModSpecs($options['modSpecs'] ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeType() : string
    {
        return 'modify';
    }

    /**
     * Sets new array of [ModSpecInterface](\.\./ModSpecInterface.html) objects.
     *
     * @param  array $modSpecs
     * @return object $this
     */
    public function setModSpecs(array $modSpecs)
    {
        $this->modSpecs = $modSpecs;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModSpecs() : array
    {
        return $this->modSpecs;
    }

    /**
     * {@inheritdoc}
     */
    public function acceptRecordVisitor(RecordVisitorInterface $visitor)
    {
        return $visitor->visitModifyRecord($this);
    }
}

// vim: syntax=php sw=4 ts=4 et:
