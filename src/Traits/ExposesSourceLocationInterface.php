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

use Korowai\Lib\Ldif\SourceLocationInterface;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait ExposesSourceLocationInterface
{
    /**
     * Returns the SourceLocationInterface instance wrapped by this object.
     *
     * @return SourceLocationInterface|null
     */
    abstract public function getSourceLocation() : ?SourceLocationInterface;

    /**
     * Returns the source file name as string.
     *
     * @return string
     */
    public function getSourceFileName() : string
    {
        return $this->getSourceLocation()->getSourceFileName();
    }

    /**
     * Returns the whole source string.
     *
     * @return string
     */
    public function getSourceString() : string
    {
        return $this->getSourceLocation()->getSourceString();
    }

    /**
     * Returns zero-based byte offset in the source string at the location.
     *
     * @return int
     */
    public function getSourceOffset() : int
    {
        return $this->getSourceLocation()->getSourceOffset();
    }

    /**
     * Returns zero-based (multibyte) character offset of the source character
     * at the location.
     *
     * @return int
     */
    public function getSourceCharOffset(string $encoding = null) : int
    {
        return $this->getSourceLocation()->getSourceCharOffset(...(func_get_args()));
    }

    /**
     * Returns zero-based source line index of the line at location.
     *
     * @return int
     */
    public function getSourceLineIndex() : int
    {
        return $this->getSourceLocation()->getSourceLineIndex();
    }

    /**
     * Returns the source line at location as string.
     *
     * @param  int $index Zero-based line index of the line to be returned. If
     *                   not given, an implementation should use the value
     *                   returned by ``getSourceLineIndex()`` instead.
     *
     * @return string
     */
    public function getSourceLine(int $index = null) : string
    {
        return $this->getSourceLocation()->getSourceLine(...func_get_args());
    }

    /**
     * Returns the line index and byte offset (relative to the beginning of the
     * line) for the location.
     *
     * ```php
     *  [$line, $byte] = $obj->getSourceLineAndOffset();
     * ```
     *
     * @return array Two-element array with line number stored at position 0
     *               and byte offset at position 1.
     */
    public function getSourceLineAndOffset() : array
    {
        return $this->getSourceLocation()->getSourceLineAndOffset();
    }

    /**
     * Returns the line index and (multibyte) character offset (relative to the
     * beginning of the line) for the location.
     *
     * ```php
     *  [$line, $char] = $obj->getSourceLineAndCharOffset();
     * ```
     *
     * @return array Two-element array with line number stored at position 0
     *               and character offset at position 1.
     */
    public function getSourceLineAndCharOffset(string $encoding = null) : array
    {
        return $this->getSourceLocation()->getSourceLineAndCharOffset(...(func_get_args()));
    }
}

// vim: syntax=php sw=4 ts=4 et:
