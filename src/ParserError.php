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

use Korowai\Lib\Ldif\Traits\DecoratesSourceLocationInterface;

/**
 * LDIF parser error. Encapsulates error message and the location of the error
 * in source code. Can also be thrown.
 */
class ParserError extends \Exception implements ParserErrorInterface
{
    use DecoratesSourceLocationInterface;

    /**
     * Initializes the error object.
     *
     * @param SourceLocationInterface $location Error location
     * @param  string $message Error message.
     * @param  int $code User-defined code.
     * @param Exception $previous
     */
    public function __construct(
        SourceLocationInterface $location,
        string $message,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->setSourceLocation($location);
        parent::__construct($message, $code, $previous);
    }

    /**
     * Returns the error location as a string.
     *
     * @return string
     */
    public function getSourceLocationString(array $line_and_char = null) : string
    {
        [$line, $char] = ($line_and_char ?? $this->getSourceLineAndCharOffset());
        return  $this->getSourceFileName() .':'. ($line + 1) .':'. ($char + 1);
    }

    /**
     * Returns a string which consists of a number of leading spaces and the
     * ``"^"`` character.
     *
     * @param  array $line_and_char
     *
     * The position of the ``"^"`` character corresponds to the error location
     * in the source line. The typical use of the function is as
     *
     * ```
     * <?php
     * $err = new ParserError("syntax error", ...);
     * $lines = [
     *      $err->getMessage(),
     *      $err->getSourceLine(),
     *      $err->getSourceLocationIndicator()
     * ];
     * echo implode("\n", $lines) . "\n";
     * ```
     *
     * @return string
     */
    public function getSourceLocationIndicator(array $line_and_char = null)
    {
        $char = ($line_and_char ?? $this->getSourceLineAndCharOffset())[1];
        return str_repeat(' ', $char) . '^';
    }

    /**
     * Returns 3-element array of strings - elements of a multiline error message.
     *
     * Example usage:
     *
     * ```php
     * <?php
     *  $err = new ParserError("syntax error", ...);
     *  echo implode("\n", $err->getMultilineMessageArray()) . "\n";
     * ```
     *
     * @return array A 3-element array of strings, with error message at
     *               position 0, source line at position 1 and location
     *               indicator at position 2.
     */
    public function getMultilineMessageLines() : array
    {
        $line_and_char = $this->getSourceLineAndCharOffset();
        $location = $this->getSourceLocationString($line_and_char);
        return [
            $location .':'. $this->getMessage(),
            $location .':'. $this->getSourceLine($line_and_char[0]),
            $location .':'. $this->getSourceLocationIndicator($line_and_char)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMultilineMessage() : string
    {
        return implode("\n", $this->getMultilineMessageLines());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getSourceLocationString() .':'. $this->getMessage();
    }
}

// vim: syntax=php sw=4 ts=4 et:
