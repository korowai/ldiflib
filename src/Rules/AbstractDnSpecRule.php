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

use Korowai\Lib\Ldif\ParserStateInterface as State;

/**
 * A rule object that implements *dn-spec* rule defined in RFC2849.
 *
 * - semantic value: string
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
abstract class AbstractDnSpecRule extends AbstractNameSpecRule
{
    /**
     * {@inheritdoc}
     */
    public function prefix() : string
    {
        return 'dn';
    }
}

// vim: syntax=php sw=4 ts=4 et:
