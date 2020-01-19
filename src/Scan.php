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

use Korowai\Lib\Compat\Exception\PregException;
use function Korowai\Lib\Compat\preg_match;

class Scan
{
    /**
     * Matches the string starting at *$location* against *$pattern*. This is just
     * a wrapper around
     * [preg_match()](https://www.php.net/manual/en/function.preg-match.php).
     *
     * @param  string $pattern The pattern to search for, as a string.
     * @param  LocationInterface $location Provides the subject string and offset.
     * @param  int $flags Flags passed to [preg_match()](https://www.php.net/manual/en/function.preg-match.php).
     *
     * @return array
     *      Returns an array of matches as returned by
     *      [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      via its argument named *$matches*.
     * @throws PregException
     *      When [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      triggers an error or returns false.
     */
    public static function matchAt(string $pattern, LocationInterface $location, int $flags = 0) : array
    {
        $subject = $location->getString();
        $offset = $location->getOffset();
        return static::matchString($pattern, $subject, $flags, $offset);
    }

    /**
     * Matches the string starting at $cursor's position against $pattern and
     * moves the *$cursor* after the matched part of string.
     *
     * @param  string $pattern
     * @param  CursorInterface $cursor
     * @param  int $flags
     *      Passed to [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      (note: ``PREG_OFFSET_CAPTURE`` is added unconditionally).
     *
     * @return array
     *      Array of matches as returned by
     *      [preg_match()](https://www.php.net/manual/en/function.preg-match.php).
     * @throws PregException
     *      When [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      triggers an error or returns false.
     */
    public static function matchAhead(string $pattern, CursorInterface $cursor, int $flags = 0) : array
    {
        $matches = static::matchAt($pattern, $cursor, PREG_OFFSET_CAPTURE | $flags);
        if (!empty($matches)) {
            $cursor->moveTo($matches[0][1] + strlen($matches[0][0]));
        }
        return $matches;
    }

    /**
     * Matches *$subject* against *$pattern* with
     * [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     * and returns an array of matches (including capture groups).
     *
     * @param  string $pattern Regular expression passed to preg_match()
     * @param  string $subject Subject string passed to preg_match()
     * @param  int $flags Flags passed to preg_match()
     * @param  int $offset Offset passed to preg_match()
     *
     * @return array
     *      Returns an array of matches as returned by
     *      [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      via its argument named *$matches*.
     * @throws PregException
     *      When [preg_match()](https://www.php.net/manual/en/function.preg-match.php)
     *      triggers an error or returns false.
     */
    public static function matchString(string $pattern, string $subject, int $flags = 0, int $offset = 0) : array
    {
        $tail = array_slice(func_get_args(), 2);
        preg_match($pattern, $subject, $matches, ...$tail);
        return $matches;
    }

    /**
     * Returns true if *$matches[$key]* exists and is a two-element array containing
     * string at offset 0 and non-negative integer at offset 1. This corresponds to
     * a PCRE capture group *$key* being matched with the flags PREG_OFFSET_CAPTURE
     * and stored in *$matches*.
     *
     * @param  mixed $key Key identifying the capture group.
     * @param  array $matches The array of matches as returned by ``preg_match()``.
     * @param  string $string Returns the captured string  (or null).
     * @param  int $offset Returns the capture offset (or -1).
     *
     * @return bool Returns true if there is non-null capture group under *$key*.
     */
    public static function matched($key, array $matches, string &$string = null, int &$offset = null) : bool
    {
        $string = $matches[$key][0] ?? null;
        $offset = $matches[$key][1] ?? -1;
        return ($offset >= 0 && $string !== null);
    }
}

// vim: syntax=php sw=4 ts=4 et:
