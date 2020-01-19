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

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait DecoratesLocationInterface
{
    use ExposesLocationInterface;

    /**
     * @var LocationInterface
     */
    protected $location;

    /**
     * Sets instance of LocationInterface to this wrapper.
     *
     * @return $this
     */
    public function setLocation(LocationInterface $location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * Returns the encapsulated instance of LocationInterface.
     *
     * @return LocationInterface|null
     */
    public function getLocation() : ?LocationInterface
    {
        return $this->location;
    }
}

// vim: syntax=php sw=4 ts=4 et:
