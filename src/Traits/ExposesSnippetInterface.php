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

use Korowai\Lib\Ldif\SnippetInterface;
use Korowai\Lib\Ldif\LocationInterface;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait ExposesSnippetInterface
{
    use ExposesLocationInterface;

    /**
     * Returns the encapsulated instance of SnippetInterface.
     *
     * @return SnippetInterface|null
     */
    abstract public function getSnippet() : ?SnippetInterface;

    /**
     * Returns the CouledLocationInterface instance as required by ExposesLocationInterface.
     *
     * @return CouledLocationInterface|null
     */
    public function getLocation() : ?LocationInterface
    {
        return $this->getSnippet();
    }

    /**
     * Returns the snippet length in bytes.
     *
     * @return int
     */
    public function getLength() : int
    {
        return $this->getSnippet()->getLength();
    }

    /**
     * Returns the end offset of the snippet in bytes.
     *
     * @return int
     */
    public function getEndOffset() : int
    {
        return $this->getSnippet()->getEndOffset();
    }

    /**
     * Returns the length in bytes of the snippet mapped to source string.
     *
     * @return int
     */
    public function getSourceLength() : int
    {
        return $this->getSnippet()->getSourceLength();
    }

    /**
     * Returns the end offset in bytes of the snippet mapped to source string.
     *
     * @return int
     */
    public function getSourceEndOffset() : int
    {
        return $this->getSnippet()->getSourceEndOffset();
    }

    /**
     * Returns the length in characters of the snippet mapped to source string.
     *
     * @return int
     */
    public function getSourceCharLength(string $encoding = null) : int
    {
        return $this->getSnippet()->getSourceCharLength(...(func_get_args()));
    }

    /**
     * Returns the end offset in characters of the snippet mapped to source string.
     *
     * @return int
     */
    public function getSourceCharEndOffset(string $encoding = null) : int
    {
        return $this->getSnippet()->getSourceCharEndOffset(...(func_get_args()));
    }
}

// vim: syntax=php sw=4 ts=4 et:
