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

use Korowai\Lib\Ldif\LocationInterface;
use Korowai\Lib\Ldif\SourceLocationInterface;
use Korowai\Lib\Ldif\InputInterface;

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait ExposesLocationInterface
{
    use ExposesSourceLocationInterface;

    /**
     * Returns the encapsulated instance of LocationInterface.
     *
     * @return LocationInterface|null
     */
    abstract public function getLocation() : ?LocationInterface;

    /**
     * Returns the encapsulated instance of SourceLocationInterface.
     *
     * @return SourceLocationInterface
     */
    public function getSourceLocation() : ?SourceLocationInterface
    {
        return $this->getLocation();
    }

    /**
     * Returns the whole input string.
     *
     * @return string
     */
    public function getString() : string
    {
        return $this->getLocation()->getString();
    }

    /**
     * Returns zero-based byte offset in the input string of the location.
     *
     * @return int
     */
    public function getOffset() : int
    {
        return $this->getLocation()->getOffset();
    }

    /**
     * Returns whether the offset points at a character within the string.
     *
     * The method shall return the value of the following expression
     *
     * ```
     *  (getOffset() >= 0 && getOffset() < strlen(getString()))
     * ```
     *
     * @return bool
     */
    public function isValid() : bool
    {
        return $this->getLocation()->isValid();
    }

    /**
     * Returns zero-based (multibyte) character offset in the input string of the location.
     *
     * @return int
     */
    public function getCharOffset(string $encoding = null) : int
    {
        return $this->getLocation()->getCharOffset(...(func_get_args()));
    }

    /**
     * Returns the InputInterface containing the character at location.
     *
     * @return InputInterface|null
     */
    public function getInput() : InputInterface
    {
        return $this->getLocation()->getInput();
    }

    /**
     * Returns new LocationInterface instance made out of this one. The
     * returned object points to the same input at *$offset*. If *$offset* is
     * null or not given, then it's taken from this location.
     *
     * @param  int|null $offset
     *
     * @return LocationInterface
     */
    public function getClonedLocation(int $offset = null) : LocationInterface
    {
        return $this->getLocation()->getClonedLocation($offset);
    }
}

// vim: syntax=php sw=4 ts=4 et:
