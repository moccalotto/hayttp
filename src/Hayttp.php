<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp;

use Hayttp\Mock\Route;
use BadMethodCallException;
use Hayttp\Contracts\Engine;
use Hayttp\Mock\MockResponse;

/**
 * Request creation facade.
 *
 * @method Request get(string $url)     Create a GET request
 * @method Request post(string $url)    Create a POST request
 * @method Request put(string $url)     Create a PUT request
 * @method Request patch(string $url)   Create a PATCH request
 * @method Request head(string $url)    Create a HEAD request
 * @method Request delete(string $url)  Create a DELETE request
 * @method Request options(string $url) Create a OPTIONS request
 * @method MockResponse    createMockResponse($request, $route) Create a mock response via a request and a route
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
    public static function instance()
    {
        if (self::$defaultInstance === null) {
            self::$defaultInstance = new static(Request::class);
        }

        return self::$defaultInstance;
    }

    /**
     * Add a mocked end point to all requests created by the global Hayttp facade.
     *
     * Mocking end points does not necessarily mean that all HTTP calls will be
     * intercepted. In order to mock all end points, you must add a catch-all
     * end point. Like
     *      mockEndpoint(
     *          '.*',          // match any method
     *          '{anything}',  // match any URL
     *          $handler
     *      )
     *
     *
     * @param string   $methodPattern a regular expression to match the method(s) of the call
     * @param string   $urlPattern    A url "pattern". For instance {protocol}://example.{tld}/foo/{dir1}/{dir2}
     * @param callable $handler       A callable that returns a Response object
     *
     * @return Hayttp
     */
    public static function mockEndpoint($methodPattern, $urlPattern, $handler)
    {
        return static::instance()
            ->withMockedEndpoint($methodPattern, $urlPattern, $handler)
            ->setAsGlobal();
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
     * @return Request
     */
    public function createRequest($method, $url)
    {
        $request = new Request(
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
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     *
     * @throws BadMethodCallException if $methodName is incorrect
     */
    public function __call($methodName, $args)
    {
        $method = strtoupper($methodName);

        if (in_array($method, ['GET', 'POST', 'PUT', 'PATCH', 'HEAD', 'DELETE', 'OPTIONS'])) {
            return $this->createRequest($method, $args[0]);
        }

        throw new BadMethodCallException(sprintf('Unknown method »%s«', $methodName));
    }

    /**
     * Forward calls to default instance.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return mixed
     *
     * @throws BadMethodCallException if $methodName is incorrect
     */
    public static function __callStatic($methodName, $args)
    {
        return call_user_func_array([static::instance(), $methodName], $args);
    }

    /**
     * Create an empty mock response from a given request and route.
     *
     * @param Request $request the request made to the mocked end point
     * @param Route   $route   Routing of parameters passed to the handler
     *
     * @return MockResponse
     */
    public static function createMockResponse(Request $request, Route $route)
    {
        return MockResponse::new($request, $route);
    }

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return Hayttp
     */
    protected function with(string $property, $value)
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
    public function withDeferredCall(string $methodName, array $args = [])
    {
        $tmp = $this->deferredCalls;
        $tmp[] = [$methodName, $args];

        return $this->with('deferredCalls', $tmp);
    }

    /**
     * Add a mocked end point to all requests created.
     *
     * @param string   $methodPattern
     * @param string   $urlPattern
     * @param callable $handler
     *
     * @return Hayttp
     */
    public function withMockedEndpoint()
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * All requests will have this mount point prepended to their url.
     *
     * @param string $url
     *
     * @return Hayttp
     */
    public function withMountPoint(string $url)
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
    public function withTimeout()
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
    public function withUserAgent()
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
    public function withCryptoMethod()
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Disable all SSL certificate checks.
     *
     * @return Hayttp
     */
    public function skipCertificateChecks()
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }

    /**
     * Set the transfer engine.
     *
     * @param Engine $engine
     *
     * @return Hayttp
     */
    public function withEngine()
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
    public function withHeaders()
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
    public function withAdditionalHeaders()
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
    public function withProxy()
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
    public function withHeader()
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
    public function withTls()
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
    public function withBasicAuth()
    {
        return $this->withDeferredCall(__FUNCTION__, func_get_args());
    }
}
