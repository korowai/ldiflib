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

/**
 * State object for Parser.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class ParserState implements ParserStateInterface
{
    /**
     * @var CursorInterface
     */
    protected $cursor;

    /**
     * @var VersionSpec
     */
    protected $versionSpec;

    /**
     * @var array
     */
    protected $records;

    /**
     * @var array
     */
    protected $errors;

    /**
     * Initializes the ParserState object.
     *
     * @param  CursorInterface $cursor
     * @param  array|null $errors
     * @param  array|null $records
     * @param  VersionSpecInterface|null $versionSpec
     */
    public function __construct(
        CursorInterface $cursor,
        array $errors = null,
        array $records = null,
        VersionSpecInterface $versionSpec = null
    ) {
        $this->initParserState($cursor, $errors, $records, $versionSpec);
    }

    /**
     * {@inheritdoc}
     */
    public function getCursor() : CursorInterface
    {
        return $this->cursor;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecords() : array
    {
        return $this->records;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersionSpec() : ?VersionSpecInterface
    {
        return $this->versionSpec;
    }

    /**
     * {@inheritdoc}
     */
    public function isOk() : bool
    {
        return count($this->errors) === 0;
    }

    /**
     * Sets the instance of CursorInterface to this object.
     *
     * @param  CursorInterface $cursor
     * @return object $this
     */
    public function setCursor(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
        return $this;
    }

    /**
     * Replaces the errors array with new one.
     *
     * @param  array $errors
     * @return object $this
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Replaces the records array with new one.
     *
     * @param  array $records
     * @return object $this
     */
    public function setRecords(array $records)
    {
        $this->records = $records;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function appendError(ParserErrorInterface $error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function errorHere(string $message, array $arguments = [])
    {
        $error = new ParserError($this->getCursor()->getClonedLocation(), $message, ...$arguments);
        return $this->appendError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function errorAt(int $offset, string $message, array $arguments = [])
    {
        $error = new ParserError($this->getCursor()->getClonedLocation($offset), $message, ...$arguments);
        return $this->appendError($error);
    }

    /**
     * {@inheritdoc}
     */
    public function appendRecord(RecordInterface $record)
    {
        $this->records[] = $record;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setVersionSpec(?VersionSpecInterface $versionSpec)
    {
        $this->versionSpec = $versionSpec;
        return $this;
    }

    /**
     * Initializes the ParserState object
     *
     * @param  CursorInterface $cursor
     * @param  array|null $errors
     * @param  array|null $records
     * @param  VersionSpecInterface|null $versionSpec
     */
    protected function initParserState(
        CursorInterface $cursor,
        array $errors = null,
        array $records = null,
        VersionSpecInterface $versionSpec = null
    ) {
        $this->setCursor($cursor);
        $this->setErrors($errors ?? []);
        $this->setRecords($records ?? []);
        $this->setVersionSpec($versionSpec);
    }
}

// vim: syntax=php sw=4 ts=4 et:
