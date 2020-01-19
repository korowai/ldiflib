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

use Korowai\Lib\Rfc\Rfc3986;
use League\Uri\Uri;
use League\Uri\Contracts\UriInterface;
use League\Uri\Exceptions\SyntaxError;
use function Korowai\Lib\Context\with;
use function Korowai\Lib\Error\exceptionErrorHandler;

/**
 * Semantic value of RFC2849 ``value-spec`` rule. One of SAFE-STRING,
 * BASE64-STRING or URL.
 */
final class Value implements ValueInterface
{
    /**
     * @var int
     */
    private $type;

    /**
     * @var string|UriInterface
     */
    private $spec;

    /**
     * @var string
     */
    private $content;

    /**
     * Creates Value from SAFE-STRING.
     *
     * @param  string $string
     * @return ValueInterface
     */
    public static function createSafeString(string $string) : ValueInterface
    {
        return new self(self::TYPE_SAFE, $string);
    }

    /**
     * Creates Value from BASE64-STRING.
     *
     * @param  string $b64string
     * @param  string $decoded
     * @return ValueInterface
     */
    public static function createBase64String(string $b64string, string $decoded = null) : ValueInterface
    {
        return new self(self::TYPE_BASE64, $b64string, $decoded);
    }

    /**
     * Creates Value from preg matches obtained by parsing an URL. It's
     * assumed, that matches were obtained from ``preg_match()`` invoked with
     * PREG_OFFSET_CAPTURE flag.
     *
     * @param  array $matches
     * @return ValueInterface
     * @throws SyntaxError
     *      if the URI is in an invalid state according to RFC3986 or to scheme
     *      specific rules.
     */
    public static function createUriFromRfc3986Matches(array $matches) : ValueInterface
    {
        $matches = Rfc3986::findCapturedValues('URI_REFERENCE', $matches);

        // Most of the $matches map one-to-one into $components...
        $components = array_combine(array_keys($matches), array_column($matches, 0));

        // ... but some of them need a little bit of tweaking
        if (null !== ($components['userinfo'] ?? null)) {
            [$user, $pass] = explode(':', $components['userinfo'], 2) + [1 => null];
            $components['user'] = $user;
            $components['pass'] = $pass;
        }

        if (null !== ($port = $components['port'] ?? null)) {
            if ((string)(int)$port !== (string)$port) {
                throw new SyntaxError(sprintf('The port `%s` is invalid (not an integer)', $port));
            }
            $components['port'] = (int)$port;
        }

        $components['path'] =
            $components['path_abempty'] ??
            $components['path_absolute'] ??
            $components['path_rootless'] ??
            $components['path_noscheme'] ??
            $components['path_empty'] ?? null;

        return self::createUriFromComponents($components);
    }

    /**
     * Creates Value form components that represent an URI.
     *
     * @param  array $components
     * @return ValueInterface
     */
    public static function createUriFromComponents(array $components) : ValueInterface
    {
        $uri = Uri::createFromComponents($components);
        return static::createUri($uri);
    }

    /**
     * Creates Value from Uri object.
     *
     * @param  Uri $uri
     * @return ValueInterface
     */
    public static function createUri(UriInterface $uri) : ValueInterface
    {
        return new self(self::TYPE_URL, $uri);
    }

    /**
     * Initializes the Value object.
     *
     * @param  int $type
     * Must be one of
     *
     * - ``ValueInterface::TYPE_SAFE``,
     * - ``ValueInterface::TYPE_BASE64``, or
     * - ``ValueInterface::TYPE_URL``.
     *
     * @param  mixed $spec
     * Specifies the value to be encapsulated. Must be consistent with *$type*,
     * as follows:
     *
     * - must be a string when *$type* is ``ValueInterface::TYPE_SAFE``,
     * - must be a string when *$type* is ``ValueInterface::TYPE_BASE64``,
     *   contains the base64-encoded content string,
     * - must be an instance of ``\League\Uri\UriInterface`` when *$type* is
     *   ``ValueInterface::TYPE_URL``.
     *
     * @param  string $content
     *      The value to be returned by getContent(). If set, the following
     *      conventions apply:
     *
     * - when *$type* is ``ValueInterface::TYPE_SAFE``, it shall be same as *$spec*,
     * - when *$type* is ``ValueInterface::TYPE_BASE64``, it shall be the decoded *$spec*,
     * - when *$type* is ``ValueInterface::TYPE_URL``, it shall be the contents
     *   of the file pointed to by the *$spec* URI.
     */
    private function __construct(int $type, $spec, string $content = null)
    {
        $this->type = $type;
        $this->spec = $spec;
        $this->content = $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpec()
    {
        return $this->spec;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent() : string
    {
        if (!isset($this->content)) {
            $this->content = static::fetchContent($this->getType(), $this->getSpec());
        }
        return $this->content;
    }

    /**
     * Returns the content *$value* of given *$type*.
     *
     * @param  int $type
     * @param  mixed $spec
     * @return string
     */
    private static function fetchContent(int $type, $spec) : string
    {
        static $fetchMethods = [
            self::TYPE_SAFE   => 'fetchSafeContent',
            self::TYPE_BASE64 => 'fetchBase64Content',
            self::TYPE_URL    => 'fetchUriContent',
        ];

        $method = [static::class, ($fetchMethods[$type] ?? 'throwInvalidTypeException')];

        return call_user_func($method, $type, $spec);
    }

    /**
     * Returns the *$string* as is.
     *
     * @param  int $type
     * @param  string $string
     * @return string
     */
    private static function fetchSafeContent(int $type, string $string) : string
    {
        return $string;
    }

    /**
     * Decodes base64-encoded string.
     *
     * @param  int $type
     * @param  string $b64string
     * @return string
     */
    private static function fetchBase64Content(int $type, string $b64string) : string
    {
        $content = base64_decode($b64string, true);
        if ($content === false) {
            // FIXME: dedicated exception.
            throw new \RuntimeException('failed to decode base64 string');
        }
        return $content;
    }

    /**
     * Retrieves content referenced by *$uri*.
     *
     * @param  int $type
     * @param  UriInterface $uri
     * @return string
     */
    private static function fetchUriContent(int $type, UriInterface $uri) : string
    {
        // FIXME: dedicated exception?
        return with(exceptionErrorHandler(\ErrorException::class), $uri)(function ($eh, $uri) {
            return file_get_contents((string)$uri);
        });
    }

    /**
     * Unconditionaly throws an exception.
     *
     * @param  int $type
     * @throws \RuntimeException
     */
    public static function throwInvalidTypeException(int $type)
    {
        // FIXME: dedicated exception.
        throw new \RuntimeException('internal error: invalid type ('.$type.') set to '.self::class.' object');
    }
}

// vim: syntax=php sw=4 ts=4 et:
