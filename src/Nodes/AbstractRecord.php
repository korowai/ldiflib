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

use Korowai\Lib\Ldif\RecordInterface;
use Korowai\Lib\Ldif\SnippetInterface;
use Korowai\Lib\Ldif\Traits\HasSnippet;

/**
 * An abstract base class for parsed LDIF records.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractRecord implements RecordInterface
{
    use HasSnippet;

    /**
     * @var string
     */
    protected $dn;

    /**
     * Initializes the object. Should be invoked from subclass' constructor.
     *
     * @param  string $dn
     *      Distinguished name of the entry being altered by the record.
     * @param  array $options
     *      An array of key => value pairs. Supported options are:
     *
     * - ``"snippet" => SnippetInterface`` (optional): an optional
     *   instance of [SnippetInterface](\.\./SnippetInterface.html) to be
     *   attached to this record.
     *
     * Unsupported keys are silently ignored.
     *
     * @return object $this
     */
    public function initAbstractRecord(string $dn, array $options = [])
    {
        $this->setDn($dn);
        $this->setSnippet($options['snippet'] ?? null);
        return $this;
    }

    /**
     * Sets new DN to this object.
     *
     * @param  string $dn
     *
     * @return object $this
     */
    public function setDn(string $dn)
    {
        $this->dn = $dn;
        return $this;
    }

    /**
     * Returns the value of DN as string.
     *
     * @return string
     */
    public function getDn() : string
    {
        return $this->dn;
    }
}

// vim: syntax=php sw=4 ts=4 et:
