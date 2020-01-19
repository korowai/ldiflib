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
 * LDIF preprocessor.
 */
class Preprocessor implements PreprocessorInterface
{
    /**
     * Assembles pieces produced by rmRe() and updates index map *$im* accordingly.
     *
     * @param  array $pieces
     * @param  IndexMap $im
     *
     * @return string Imploded pieces
     */
    public static function asmPieces(array $pieces, IndexMap $im) : string
    {
        $new_im = IndexMap::arrayFromPieces($pieces);
        $im->combineWithArray($new_im);
        return implode(array_map(function ($p) {
            return $p[0];
        }, $pieces));
    }

    /**
     * Removes parts of the string that match a regular expression *$re*.
     *
     * @param  string $re the regular expression to be matched
     * @param  string $src the original string
     * @param  IndexMap $im an IndexMap object
     *
     * @return string new string with removed parts that matched $re.
     */
    public static function rmRe(string $re, string $src, IndexMap $im) : string
    {
        $flags = PREG_SPLIT_OFFSET_CAPTURE | PREG_SPLIT_NO_EMPTY;
        $pieces = preg_split($re, $src, -1, $flags);
        return static::asmPieces($pieces, $im);
    }

    /**
     * Removes line continuations from LDIF text (unfolds the lines).
     *
     * @param  string $src input string to be unfolded
     * @param  IndexMap $im an IndexMap object
     *
     * @return string the resultant string with line continuations removed
     */
    public static function rmLnCont(string $src, IndexMap $im) : string
    {
        return static::rmRe('/(?:\r\n|\n) /mu', $src, $im);
    }

    /**
     * Removes comment lines from LDIF text. This should be used after line
     * unfolding (see rmLnCont()).
     *
     * @param  string $src input string to be stripped out from comments
     * @param  IndexMap $im an IndexMap object
     *
     * @return string the resultant string with comments removed
     */
    public static function rmComments(string $src, IndexMap $im) : string
    {
        return static::rmRe('/^#(?:[^\r\n])*(?:\r\n|\n)?/mu', $src, $im);
    }

    /**
     * {@inheritdoc}
     */
    public function preprocess(string $source, string $filename = null) : InputInterface
    {
        $string = static::rmLnCont($source, ($im = new IndexMap([])));
        $string = static::rmComments($string, $im);
        return new Input($source, $string, $im, $filename);
    }
}

// vim: syntax=php sw=4 ts=4 et:
