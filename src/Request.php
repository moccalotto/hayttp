<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp;

use LogicException;
use Hayttp\Contracts\Engine;
use Hayttp\Contracts\Payload;
use Hayttp\Mock\Endpoint as MockedEndpoint;

/**
 * HTTP Request class.
 */
class Request
{
    use Traits\Common\Extendable {
        __call as callExtension;
        __callStatic as callStaticExtension;
    }
    use Traits\Common\DebugInfo;
    use Traits\Request\CanSend;
    use Traits\Request\HasWithMethods;
    use Traits\Request\Accessors;
    use Traits\Request\ExpectsCommonMimeTypes;
    use Traits\Request\Multipart;
    use Traits\Request\DeprecatedMethods;

    const CRYPTO_ANY = 'CRYPTO_ANY';
    const CRYPTO_SSLV3 = 'CRYPTO_SSLV3';
    const CRYPTO_TLS = 'CRYPTO_TLS';
    const CRYPTO_TLS_1_0 = 'CRYPTO_TLS_1_0';
    const CRYPTO_TLS_1_1 = 'CRYPTO_TLS_1_1';
    const CRYPTO_TLS_1_2 = 'CRYPTO_TLS_1_2';
    const CRYPTO_METHODS = 'CRYPTO_ANY, CRYPTO_SSLV3, CRYPTO_TLS, CRYPTO_TLS_1_0, CRYPTO_TLS_1_1, CRYPTO_TLS_1_2';

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var string
     */
    protected $userAgent = 'Hayttp';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var Payload
     */
    protected $payload;

    /**
     * @var string|null
     */
    protected $proxy;

    /**
     * @var bool
     */
    protected $secureSsl = true;

    /**
     * @var float
     */
    protected $timeout = 5;

    /**
     * @var string
     */
    protected $cryptoMethod = self::CRYPTO_TLS_1_2;

    /**
     * @var array
     */
    protected $responseCalls = [];

    /**
     * @var MockedEndpoint[]
     */
    protected $mockedEndpoints = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return self
     */
    protected function with($property, $value)
    {
        $clone = clone $this;

        $clone->$property = $value;

        return $clone;
    }

    /**
     * Format headers.
     *
     * Only one hosts header, which must be the first header
     * Only one User Agent header.
     * All other headers are copied verbatim.
     *
     * @return string[]
     */
    public function preparedHeaders()
    {
        $headers = $this->headers;

        $preparedHeaders = [];

        // Make sure the host header is the very first header.
        if (isset($headers['Host'])) {
            $preparedHeaders[] = sprintf('Host: %s', $headers['Host']);
            unset($headers['Host']);
        } else {
            $preparedHeaders[] = sprintf('Host: %s', parse_url($this->url, PHP_URL_HOST));
        }

        if (!isset($headers['User-Agent'])) {
            $preparedHeaders[] = sprintf('User-agent: %s', $this->userAgent);
        }

        if (!isset($headers['Content-Type']) && $this->payload instanceof Payload) {
            $preparedHeaders[] = sprintf('Content-Type: %s', $this->payload->contentType());
        }

        /** @var string $name, @var string $header */
        foreach ($headers as $name => $header) {
            $preparedHeaders[] = sprintf('%s: %s', $name, $header);
        }

        return $preparedHeaders;
    }

    /**
     * Constructor.
     *
     * @param string $method
     * @param string $url
     */
    public function __construct($method, $url)
    {
        $this->method = (string) $method;
        $this->url = (string) $url;
    }

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Setter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws LogicException whenever setting a non-existing property is attempted
     */
    public function __set($name, $value)
    {
        throw new LogicException(sprintf(
            'Cannot set "%s". This object is immutable',
            $name
        ));
    }

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function render()
    {
        $headers = $this->preparedHeaders();

        $parsedUrl = parse_url($this->url);

        $init = sprintf(
            '%s %s%s HTTP/1.0',
            $this->method,
            $parsedUrl['path'] ?? '/',
            $parsedUrl['query'] ?? ''
        );

        $crlf = "\r\n";

        return $init
            . $crlf
            . implode($crlf, $headers)
            . $crlf . $crlf
            . $this->payload;
    }

    /**
     * Get the calls that are to be executed on the response
     * as soon as we have one.
     *
     * @return array
     */
    public function responseCalls()
    {
        return $this->responseCalls;
    }

    /**
     * Magic Method for dynamic method names.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($methodName, $args)
    {
        if (static::hasExtension($methodName)) {
            return static::callExtension($methodName, $args);
        }

        return $this->withResponseCall($methodName, $args);
    }

    /**
     * Factory.
     *
     * Initialize a DELETE request
     *
     * @param string $url
     *
     * @return self
     */
    public static function delete($url)
    {
        return new static('DELETE', $url);
    }

    /**
     * Factory.
     *
     * Initialize a GET request
     *
     * @param string $url
     *
     * @return self
     */
    public static function get($url)
    {
        return new static('GET', $url);
    }

    /**
     * Factory.
     *
     * Initialize a HEAD request
     *
     * @param string $url
     *
     * @return self
     */
    public static function head($url)
    {
        return new static('HEAD', $url);
    }

    /**
     * Factory.
     *
     * Initialize a OPTIONS request
     *
     * @param string $url
     *
     * @return self
     */
    public static function options($url)
    {
        return new static('OPTIONS', $url);
    }

    /**
     * Factory.
     *
     * Initialize a PATCH request
     *
     * @param string $url
     *
     * @return self
     */
    public static function patch($url)
    {
        return new static('PATCH', $url);
    }

    /**
     * Factory.
     *
     * Initialize a POST request
     *
     * @param string $url
     *
     * @return self
     */
    public static function post($url)
    {
        return new static('POST', $url);
    }

    /**
     * Factory.
     *
     * Initialize a PUT request
     *
     * @param string $url
     *
     * @return self
     */
    public static function put($url)
    {
        return new static('PUT', $url);
    }
}
