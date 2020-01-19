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

use Korowai\Lib\Ldif\Traits\HasSnippet;

/**
 * @todo Write documentation
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class VersionSpec implements VersionSpecInterface
{
    use HasSnippet;

    /**
     * @var int
     */
    protected $version;

    /**
     * Initializes the object.
     *
     * @param SnippetInterface $snippet
     * @param  int $version
     */
    public function __construct(SnippetInterface $snippet, int $version)
    {
        $this->setSnippet($snippet);
        $this->setVersion($version);
    }

    /**
     * Sets version number.
     *
     * @param  int $version
     *
     * @return object $this
     */
    public function setVersion(int $version)
    {
        $this->version = $version;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion() : int
    {
        return $this->version;
    }
}

// vim: syntax=php sw=4 ts=4 et:
