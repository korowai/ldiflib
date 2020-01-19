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

/**
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
trait HasSnippet
{
    /**
     * @var SnippetInterface
     */
    protected $snippet;

    /**
     * Sets instance of SnippetInterface to this wrapper.
     *
     * @param  SnippetInterface|null $snippet
     *
     * @return $this
     */
    public function setSnippet(?SnippetInterface $snippet)
    {
        $this->snippet = $snippet;
        return $this;
    }

    /**
     * Returns the encapsulated instance of SnippetInterface.
     *
     * @return SnippetInterface|null
     */
    public function getSnippet() : ?SnippetInterface
    {
        return $this->snippet;
    }
}

// vim: syntax=php sw=4 ts=4 et:
