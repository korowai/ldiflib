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
 * Decorator design pattern with
 * [SourceLocationInterface](\.\./SourceLocationInterface.html)
 * as a decorated (wrapped) type.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait DecoratesSourceLocationInterface
{
    use ExposesSourceLocationInterface;

    /**
     * @var SourceLocationInterface
     */
    protected $location;

    /**
     * Sets the SourceLocationInterface instance to this object.
     *
     * @param SourceLocationInterface $location
     *
     * @return $this
     */
    public function setSourceLocation(SourceLocationInterface $location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Returns the SourceLocationInterface instance wrapped by this object.
     *
     * @return SourceLocationInterface|null
     */
    public function getSourceLocation() : ?SourceLocationInterface
    {
        return $this->location;
    }
}

// vim: syntax=php sw=4 ts=4 et:
