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
use Korowai\Lib\Ldif\Traits\HasAttrValSpecs;

/**
 * Represents [RFC2849](https://tools.ietf.org/html/rfc2849)
 * *ldif-change-record* of type *change-add*.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AddRecord extends AbstractChangeRecord implements AddRecordInterface
{
    use HasAttrValSpecs;

    /**
     * Initializes the object.
     *
     * @param  string $dn
     *      Distinguished name of the entry being altered by the record.
     * @param  array $options
     *      An array of key => value pairs. Supported options are:
     *
     * - ``"attrValSpecs" => AttrValInterface[]`` (optional): an optional
     *   array of [AttrValInterface](\.\./AttrValInterface.html) objects
     *   specifying new attribute-value pairs for the entry being
     *   modified.
     * - ``"controls" => ControlInterface[]`` (optional): an optional
     *   array of controls for the operation.
     * - ``"snippet" => SnippetInterface`` (optional): an optional
     *   instance of SnippetInterface to be attached to this record.
     *
     * Unsupported keys are silently ignored.
     */
    public function __construct(string $dn, array $options = [])
    {
        parent::initAbstractChangeRecord($dn, $options);
        $this->setAttrValSpecs($options['attrValSpecs'] ?? []);
    }

    /**
     * {@inheritdoc}
     */
    public function getChangeType() : string
    {
        return 'add';
    }

    /**
     * {@inheritdoc}
     */
    public function acceptRecordVisitor(RecordVisitorInterface $visitor)
    {
        return $visitor->visitAddRecord($this);
    }
}

// vim: syntax=php sw=4 ts=4 et:
