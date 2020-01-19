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

use Korowai\Lib\Ldif\Traits\DecoratesLocationInterface;

/**
 * Represents a code snippet with well defined input, beginning location and
 * length.
 *
 * @author Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 */
class Snippet implements SnippetInterface
{
    use DecoratesLocationInterface;

    /**
     * @var int
     */
    protected $length;

    /**
     * Creates snippet out of begin and end locations.
     *
     * @param  LocationInterface $begin
     * @param  LocationInterface $end
     * @return Snippet
     */
    public static function createFromLocations(LocationInterface $begin, LocationInterface $end) : Snippet
    {
        if ($begin->getInput() !== $end->getInput()) {
            // FIXME: dedicated exception
            $call = __class__.'::'.__function__.'($begin, $end)';
            $message = 'Arguments $begin and $end in '.$call.' must satisfy $begin->getInput() === $end->getInput().';
            throw new \InvalidArgumentException($message);
        }
        return new self($begin, $end->getOffset() - $begin->getOffset());
    }

    /**
     * Creates snippet out of beginning location and parser state.
     *
     * @param  LocationInterface $begin
     * @param  ParserStateInterface $state
     * @return Snippet
     */
    public static function createFromLocationAndState(LocationInterface $begin, ParserStateInterface $state) : Snippet
    {
        return self::createFromLocations($begin, $state->getCursor());
    }

    /**
     * Initializes the object.
     *
     * @param  LocationInterface $location
     * @param  int $length
     */
    public function __construct(LocationInterface $location, int $length)
    {
        $this->init($location, $length);
    }

    /**
     * Initializes the object.
     *
     * @param  LocationInterface $location
     * @param  int $length
     *
     * @return Snippet $this
     */
    public function init(LocationInterface $location, int $length)
    {
        $this->setLocation($location);
        $this->setLength($length);
        return $this;
    }

    /**
     * Sets the length of snippet.
     *
     * @param  int $length
     * @return Snippet $this
     */
    public function setLength(int $length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength() : int
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function getEndOffset() : int
    {
        return $this->getOffset() + $this->getLength();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLength() : int
    {
        return $this->getSourceEndOffset() - $this->getSourceOffset();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceEndOffset() : int
    {
        return $this->getInput()->getSourceOffset($this->getEndOffset());
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceCharLength(string $encoding = null) : int
    {
        $args = func_get_args();
        return $this->getSourceCharEndOffset(...$args) - $this->getSourceCharOffset(...$args);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceCharEndOffset(string $encoding = null) : int
    {
        $args = func_get_args();
        return $this->getInput()->getSourceCharOffset($this->getEndOffset(), ...$args);
    }
}

// vim: syntax=php sw=4 ts=4 et:
