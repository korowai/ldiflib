<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);

namespace Korowai\Lib\Ldif;

use Korowai\Lib\Ldif\Traits\HasAttrValSpecs;
use Korowai\Lib\Ldif\Exception\InvalidModTypeException;

/**
 * Represents [RFC2849](https://tools.ietf.org/html/rfc2849)
 * *mod-spec*.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ModSpec implements ModSpecInterface
{
    use HasAttrValSpecs;

    /**
     * @var string
     */
    private $modType;

    /**
     * @var string
     */
    private $attribute;

    /**
     * Initializes the object.
     *
     * @param  string $modType
     * @param  string $attribute
     * @param  array $attrValSpecs
     *
     * @throws InvalidModTypeException
     */
    public function __construct(
        string $modType,
        string $attribute,
        array $attrValSpecs = []
    ) {
        $this->setModType($modType);
        $this->setAttribute($attribute);
        $this->setAttrValSpecs($attrValSpecs);
    }

    /**
     * Sets modType. Allowed values of *$modType* are ``"add"``, ``"delete"``,
     * and ``"replace"``.
     *
     * @param  string $modType
     * @return object $this
     * @throws InvalidModTypeException
     */
    public function setModType(string $modType)
    {
        if (!in_array(strtolower($modType), ['add', 'delete', 'replace'])) {
            $message = 'Argument 1 to '.__class__.'::setModType() must be one of "add", "delete", or "replace", "'.
                       $modType.'" given.';
            throw new InvalidModTypeException($message);
        }
        $this->modType = strtolower($modType);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getModType() : string
    {
        return $this->modType;
    }

    /**
     * Set the attribute name.
     *
     * @param  string $attribute
     * @return object $this
     */
    public function setAttribute(string $attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute() : string
    {
        return $this->attribute;
    }
}

// vim: syntax=php sw=4 ts=4 et:
