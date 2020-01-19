<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif\Rules;

use Korowai\Lib\Rfc\Rfc2849;

/**
 * A rule object that implements *dn-spec* rule defined in RFC2849.
 *
 * - semantic value: string
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
final class DnSpecRule extends AbstractDnSpecRule
{
    /**
     * Initializes the object.
     */
    public function __construct()
    {
        parent::__construct(Rfc2849::class, 'DN_SPEC');
    }
}

// vim: syntax=php sw=4 ts=4 et:
