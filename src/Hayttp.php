<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

/**
 * Request creation facade.
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
     * Create a GET request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function get(string $url) : RequestContract
    {
        return static::instance()->createRequest('GET', $url);
    }

    /**
     * Create a POST request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function post(string $url) : RequestContract
    {
        return static::instance()->createRequest('POST', $url);
    }

    /**
     * Create a PUT request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function put(string $url) : RequestContract
    {
        return static::instance()->createRequest('PUT', $url);
    }

    /**
     * Create a DELETE request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function delete(string $url) : RequestContract
    {
        return static::instance()->createRequest('DELETE', $url);
    }

    /**
     * Create a PATCH request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function patch(string $url) : RequestContract
    {
        return static::instance()->createRequest('PATCH', $url);
    }

    /**
     * Create a OPTIONS request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function options(string $url) : RequestContract
    {
        return static::instance()->createRequest('OPTIONS', $url);
    }

    /**
     * Create a HEAD request.
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function head(string $url) : RequestContract
    {
        return static::instance()->createRequest('HEAD', $url);
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
     * Having created a request, apply these calls to the
     *
     * @param string $methodName
     * @param array $args
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
    public function withTimeout(float $seconds) : Hayttp
    {
        return $this->withDeferredCall('withTimeout', [$seconds]);
    }

    /**
     * Set the user agent header.
     *
     * @param string $userAgent
     *
     * @return RequestContract
     */
    public function withUserAgent(string $userAgent) : Hayttp
    {
        return $this->withDeferredCall('withUserAgent', $userAgent);
    }
}
