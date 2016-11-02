<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use LogicException;
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
     * Get the default/global instance
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
     * Constructor
     *
     * @param string $requestFqcn The fqcn of a class that implements the RequestContract interface.
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
     */
    public function createRequest(string $method, string $url) : RequestContract
    {
        $class = $this->requestFqcn;

        return new $class(strtoupper($method), $url);
    }

    /**
     * Create a GET request
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
     * Create a POST request
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
     * Create a PUT request
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
     * Create a DELETE request
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
     * Create a PATCH request
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
     * Create a OPTIONS request
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
     * Create a HEAD request
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function head(string $url) : RequestContract
    {
        return static::instance()->createRequest('HEAD', $url);
    }
}
