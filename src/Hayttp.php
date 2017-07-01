<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use BadMethodCallException;
use Moccalotto\Hayttp\Mock\MockResponse;
use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

/**
 * Request creation facade.
 *
 * @method \Moccalotto\Hayttp\Contracts\Request get(string $url)     Create a GET request
 * @method \Moccalotto\Hayttp\Contracts\Request post(string $url)    Create a POST request
 * @method \Moccalotto\Hayttp\Contracts\Request put(string $url)     Create a PUT request
 * @method \Moccalotto\Hayttp\Contracts\Request patch(string $url)   Create a PATCH request
 * @method \Moccalotto\Hayttp\Contracts\Request head(string $url)    Create a HEAD request
 * @method \Moccalotto\Hayttp\Contracts\Request delete(string $url)  Create a DELETE request
 * @method \Moccalotto\Hayttp\Contracts\Request options(string $url) Create a OPTIONS request
 */
class Hayttp
{
    /**
     * @var Hayttp
     */
    protected static $defaultInstance = null;

    /**
     * @var string
     */
    protected $requestFqcn;

    /**
     * @var string
     */
    public $mountPoint = '';

    /**
     * @var array
     */
    protected $deferredCalls = [];

    /**
     * Get the default/global instance.
     *
     * @return Hayttp
     */
    public static function instance() : Hayttp
    {
        if (self::$defaultInstance === null) {
            self::$defaultInstance = new static(Request::class);
        }

        return self::$defaultInstance;
    }

    /**
     * Constructor.
     *
     * @param string $requestFqcn the fqcn of a class that implements the RequestContract interface
     */
    public function __construct(string $requestFqcn)
    {
        $this->requestFqcn = $requestFqcn;
    }

    /**
     * Set this instance as the default global instance.
     *
     * The gloabal instance is the one returned by the instance() method.
     *
     * @see instance()
     */
    public function setAsGlobal()
    {
        self::$defaultInstance = $this;
    }

    /**
     * Factory.
     *
     * Create a request object of the $requestFqcn class.
     *
     * @param string $method
     * @param string $url
     *
     * @return RequestContract
     */
    public function createRequest(string $method, string $url) : RequestContract
    {
        $class = $this->requestFqcn;

        $request = new $class(
            strtoupper($method),
            Util::applyMountPoint($url, $this->mountPoint)
        );

        foreach ($this->deferredCalls as list($methodName, $args)) {
            $request = call_user_func_array([clone $request, $methodName], $args);
        }

        return $request;
    }

    /**
     * Easy request construction.
     */
    public function __call($methodName, $args)
    {
        $method = strtoupper($methodName);

        if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE', 'OPTIONS'])) {
            return $this->createRequest($method, $args[0]);
        }

        if ($methodName == 'createMockResponse') {
            return MockResponse::fromRequest($args[0]);
        }

        throw new BadMethodCallException(sprintf('Unknown method »%s«', $methodName));
    }

    /**
     * Forward calls to default instance.
     */
    public static function __callStatic($methodName, $args)
    {
        return call_user_func_array([static::instance(), $methodName], $args);
    }

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return Hayttp
     */
    protected function with(string $property, $value) : Hayttp
    {
        $clone = clone $this;
        $clone->$property = $value;

        return $clone;
    }

    /**
     * Having created a request, apply these calls to the.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return Hayttp
     */
    public function withDeferredCall(string $methodName, array $args = []) : Hayttp
    {
        $tmp = $this->deferredCalls;
        $tmp[] = [$methodName, $args];

        return $this->with('deferredCalls', $tmp);
    }

    /**
     * All requests will have this mount point prepended to their url.
     *
     * @param string $url
     *
     * @return Hayttp
     */
    public function withMountPoint(string $url) : Hayttp
    {
        return $this->with(
            'mountPoint',
            Util::ensureValidUrl($url)
        );
    }

    /**
     * All requests will have this timeout.
     *
     * @param float $seconds
     *
     * @return Hayttp
     */
    public function withTimeout() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the user agent header.
     *
     * @param string $userAgent
     *
     * @return Hayttp
     */
    public function withUserAgent() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return Hayttp
     */
    public function withCryptoMethod() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Disable all SSL certificate checks.
     *
     * @return Hayttp
     */
    public function withInsecureSsl() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the transfer engine.
     *
     * @param EngineContract $engine
     *
     * @return Hayttp
     */
    public function withEngine() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return Hayttp
     */
    public function withHeaders() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Add an array of headers.
     *
     * @param array $headers
     *
     * @return Hayttp
     */
    public function withAdditionalHeaders() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the proxy server.
     *
     * @param string $proxy URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     *
     * @return Hayttp
     */
    public function withProxy() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return Hayttp
     */
    public function withHeader() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the TLS version.
     *
     * @param string $version currently, 1.*, 1.0, 1.1 and 1.2 are supported
     *
     * @return Hayttp
     */
    public function withTls() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Add a basic authorization (which is actually an authenticaation) header.
     *
     * @param string $username
     * @param string $password
     *
     * @return Hayttp
     */
    public function withBasicAuth() : Hayttp
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }
}
