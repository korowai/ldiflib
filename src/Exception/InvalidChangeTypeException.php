<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif\Exception;

/**
 * An exception thrown when an LDIF record's changetype provided as a function
 * argument has unsupported/invalid value.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class InvalidChangeTypeException extends \InvalidArgumentException
{
}

// vim: syntax=php sw=4 ts=4 et:
