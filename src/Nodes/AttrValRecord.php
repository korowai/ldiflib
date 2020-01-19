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
 * ldif-attrval-record.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class AttrValRecord extends AbstractRecord implements AttrValRecordInterface
{
    use HasAttrValSpecs;

    /**
     * Initializes the object.
     *
     * @param  string $dn
     * @param  array $attrValSpecs
     * @param  array $options
     */
    public function __construct(string $dn, array $attrValSpecs, array $options = [])
    {
        parent::initAbstractRecord($dn, $options);
        $this->setAttrValSpecs($attrValSpecs);
    }

    /**
     * {@inheritdoc}
     */
    public function acceptRecordVisitor(RecordVisitorInterface $visitor)
    {
        return $visitor->visitAttrValRecord($this);
    }
}

// vim: syntax=php sw=4 ts=4 et:
