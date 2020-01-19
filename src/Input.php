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

use Korowai\Lib\Ldif\Util\IndexMap;
use function Korowai\Lib\Compat\preg_split;

/**
 * Preprocessed input.
 *
 * Encapsulates preprocessed string together with its original (source) string.
 * The object is able to map character offsets pointing to characters of the
 * preprocessed string onto their corresponding character offsets in the source
 * string. It also provides a service of mapping character offsets in the
 * preprocessed string onto their corresponding line numbers in the source
 * string.
 */
class Input implements InputInterface
{
    /**
     * @var string
     */
    protected $source;

    /**
     * @var string
     */
    protected $string;

    /**
     * Index map. Maps preprocessed text's character offsets onto corresponding
     * offsets in the original string.
     *
     * @var IndexMap
     */
    protected $im;

    /**
     * Input name (file name).
     *
     * @var string
     */
    protected $sourceFileName;

    /**
     * Line map. Maps source text's character offsets onto their corresponding
     * line numbers.
     *
     * @var IndexMap
     */
    protected $sourceLinesMap;

    /**
     * @var array
     */
    protected $sourceLines;

    /**
     * Initializes the object
     *
     * @param  string $source Source string
     * @param  string $string Preprocessed string
     * @param  array $im Index map produced by preprocessor
     * @param  string $sourceFileName Input name (file name)
     */
    public function __construct(string $source, string $string, IndexMap $im, string $sourceFileName = null)
    {
        $this->init($source, $string, $im, $sourceFileName);
    }

    /**
     * Initializes the object
     *
     * @param  string $source Source string
     * @param  string $string Preprocessed string
     * @param  array $im Index map produced by preprocessor
     * @param  string $sourceFileName Input name (file name)
     */
    public function init(string $source, string $string, IndexMap $im, string $sourceFileName = null)
    {
        $this->source = $source;
        $this->string = $string;
        $this->im = $im;
        $this->sourceFileName = $sourceFileName ?? '-';
        $this->sourceLines = null;
        $this->sourceLinesMap = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceString() : string
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function getString() : string
    {
        return $this->string;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceFileName() : string
    {
        return $this->sourceFileName;
    }

    /**
     * Returns the "array of im", as provided to constructor via $im
     * parameter.
     *
     * @return array
     */
    public function getIndexMap() : IndexMap
    {
        return $this->im;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getString();
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceOffset(int $i) : int
    {
        return ($this->getIndexMap())($i);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceCharOffset(int $i, string $encoding = null) : int
    {
        $offset = $this->getSourceOffset($i);
        $substr = substr($this->getSourceString(), 0, $offset);
        return mb_strlen($substr, ...(array_slice(func_get_args(), 1)));
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLines() : array
    {
        if (!isset($this->sourceLines)) {
            $this->initSourceLines($this->getSourceString());
        }
        return $this->sourceLines;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLinesCount() : int
    {
        return count($this->getSourceLines());
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLine(int $i) : string
    {
        return ($this->getSourceLines())[$i];
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLineIndex(int $i) : int
    {
        $j = $this->getSourceOffset($i);
        return ($this->getSourceLinesMap())($j);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLineAndOffset(int $i) : array
    {
        $j = $this->getSourceOffset($i);
        $map = $this->getSourceLinesMap();
        $line = $map($j, $index);
        if (isset($index)) {
            $offset = $j - ($map->getArray())[$index][0];
        } else {
            $offset = 0;
        }
        return [$line, $offset];
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceLineAndCharOffset(int $i, string $encoding = null) : array
    {
        [$line, $byte] = $this->getSourceLineAndOffset($i);
        $lineStr = $this->getSourceLine($line);
        $args = array_slice(func_get_args(), 1);
        $char = mb_strlen(substr($lineStr, 0, $byte), ...$args);
        return [$line, $char];
    }

    /**
     * Returns the sourceLinesMap array.
     *
     * The sourceLinesMap is used to map character offsets in the source
     * string onto their corresponding line numbers (zero based). The map is
     * internally used by ``getSourceLineIndex()``.
     *
     * @return InexMap
     */
    public function getSourceLinesMap() : IndexMap
    {
        if (!isset($this->sourceLinesMap)) {
            $this->initSourceLines($this->getSourceString());
        }
        return $this->sourceLinesMap;
    }

    /**
     * Sets the source file name.
     *
     * @param  string $sourceFileName
     *
     * @return Input
     */
    public function setSourceFileName(string $sourceFileName) : Input
    {
        $this->sourceFileName = $sourceFileName;
        return $this;
    }


    protected function initSourceLines(string $source)
    {
        $re = '/(?:\r\n|\n)/m';
        $pieces = preg_split($re, $source, -1, PREG_SPLIT_OFFSET_CAPTURE);

        $cnt = count($pieces);

        $lm = [[-PHP_INT_MAX, -1]];
        for ($i = 0; $i < $cnt; $i++) {
            $lm[] = [$pieces[$i][1], $i];
        }

        $this->sourceLines = array_map(function ($p) {
            return $p[0];
        }, $pieces);
        $this->sourceLinesMap = new IndexMap($lm, 0);
    }
}

// vim: syntax=php sw=4 ts=4 et:
