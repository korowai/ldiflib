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

/**
 * Represents [RFC2849](https://tools.ietf.org/html/rfc2849)
 * *ldif-change-record* of type *change-delete*.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class DeleteRecord extends AbstractChangeRecord implements DeleteRecordInterface
{
    /**
     * Initializes the object.
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
     */
    public function __construct(string $dn, array $options = [])
    {
        parent::initAbstractChangeRecord($dn, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeType() : string
    {
        return "delete";
    }

    /**
     * {@inheritdoc}
     */
    public function acceptRecordVisitor(RecordVisitorInterface $visitor)
    {
        return $visitor->visitDeleteRecord($this);
    }
}

// vim: syntax=php sw=4 ts=4 et:
