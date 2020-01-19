<?php

/*
 * This file is part of Korowai framework.
 *
 * (c) Paweł Tomulik <ptomulik@meil.pw.edu.pl>
 *
 * Distributed under MIT license.
 */

declare(strict_types=1);


namespace Korowai\Lib\Ldif\Util;

/**
 * A helper object used by the IndexMap::arrayCombine() method.
 *
 * Usage
 * ```php
 * $combined = (new IndexMapArrayCombineAlgorithm)($old, $new);
 * ```
 */
class IndexMapArrayCombineAlgorithm
{
    /**
     * The resultant index map array being produced by the algorithm.
     * @var array
     */
    protected $im;

    /**
     * Index for ``$old`` array.
     * @var int
     */
    protected $i;

    /**
     * Index for ``$new`` array.
     * @var int
     */
    protected $j;

    /**
     * New shrink (introduced by ``$new``)
     * @var int
     */
    protected $ns;

    /**
     * Old shrink (introduced by ``$old``)
     * @var int
     */
    protected $os;

    /**
     * The first input index map array for the algorithm.
     * @var array
     */
    protected $old;

    /**
     * The second input index map array for the algorithm.
     * @var array
     */
    protected $new;

    /**
     * Reset the algorithm to initial state.
     *
     * @param  array $old
     * @param  array $new
     */
    protected function reset(array $old, array $new)
    {
        $this->im = [];
        $this->i = 0;
        $this->j = 0;
        $this->os = 0;
        $this->ns = 0;
        $this->old = $old;
        $this->new = $new;
    }

    /**
     * Returns ``true`` if the algorithm already finished.
     *
     * @return bool
     */
    protected function finished() : bool
    {
        return $this->i >= count($this->old) && $this->j >= count($this->new);
    }

    /**
     * Returns ``true``, if the removal defined by ``$new[$j]`` occurs before
     * (on the left side of) ``$old[$i][0]``.
     *
     * @return bool
     */
    protected function isBefore() : bool
    {
        if ($this->j >= count($this->new)) {
            return false;
        }
        if ($this->i >= count($this->old)) {
            return true;
        }
        return ($this->new[$this->j][1] + $this->ns) < $this->old[$this->i][0];
    }

    /**
     * Returns ``true``, if the removal defined by ``$new[$j]`` does not occur
     * after (on the right side of) ``$old[$i][0]``.
     *
     * @return bool
     */
    protected function isNotAfter() : bool
    {
        if ($this->j >= count($this->new) || $this->i >= count($this->old)) {
            return false;
        }

        return !(($this->new[$this->j][0] + $this->ns) > $this->old[$this->i][0]);
    }

    /**
     * Performs algorithm's step for the case, in which the removal defined by
     * ``$new[$j]`` occurs before (on the left side of) ``$old[$i][0]``.
     */
    protected function stepBefore()
    {
        if ($this->j >= count($this->new)) {
            // this should never happen, however...
            throw new \RuntimeException("internal error");
        }
        $this->im[] = [$this->new[$this->j][0], $this->new[$this->j][1] + $this->os];
        $this->ns = ($this->new[$this->j][1] - $this->new[$this->j][0]);
        $this->j++;
    }

    /**
     * Performs algorithm's step for the case, in which the removal defined by
     * ``$new[$j]`` "encloses" ``$old[$i][0]``.
     */
    protected function stepEnclosing()
    {
        if ($this->i >= count($this->old) || $this->j >= count($this->new)) {
            // this should never happen, however...
            throw new \RuntimeException("internal error");
        }
        do {
            $this->i++;
        } while ($this->i < count($this->old) && $this->old[$this->i][0] <= ($this->new[$this->j][1] + $this->ns));
        $this->ns = ($this->new[$this->j][1] - $this->new[$this->j][0]);
        $this->os = ($this->old[$this->i-1][1] - $this->old[$this->i-1][0]);
        $this->im[] = [$this->new[$this->j][0], $this->new[$this->j][1] + $this->os];
        $this->j++;
    }

    /**
     * Performs algorithm's step for the case, in which the removal defined by
     * ``$new[$j]`` occurs after (on the right side of) ``$old[$i][0]``.
     */
    protected function stepAfter()
    {
        if ($this->i >= count($this->old)) {
            // this should never happen, however...
            throw new \RuntimeException("internal error");
        }
        $this->im[] = [$this->old[$this->i][0] - $this->ns, $this->old[$this->i][1]];
        $this->os = ($this->old[$this->i][1] - $this->old[$this->i][0]);
        $this->i++;
    }

    /**
     * Run the algorithm.
     *
     * @param $old The index map array defining previous removals from the source string.
     * @param $new The index map array defining new removals applied after $old were applied.
     *
     * @return array The resultand index map array.
     */
    public function __invoke(array $old, array $new) : array
    {
        $this->reset($old, $new);

        while (!$this->finished()) {
            if ($this->isBefore()) {
                //
                // $new[$this->j] on the left side of $old[$this->i]
                //
                $this->stepBefore();
            } elseif ($this->isNotAfter()) {
                //
                // $new[$this->j] encloses $old[$this->i] (and perhaps $old[$this->i+1], ...)
                //
                $this->stepEnclosing();
            } else {
                //
                // $new[$this->j] on the right side of $old[$this->i]
                //
                $this->stepAfter();
            }
        }
        return $this->im;
    }
}

// vim: syntax=php sw=4 ts=4 et:
