<?php

/*
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Contracts\Request as RequestContract;

trait CreatesRequests
{
    /**
     * Factory.
     *
     * Initialize a DELETE request
     *
     * @param string $url
     *
     * @return RequestContract
     */
    public static function delete($url) : RequestContract
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
     * @return RequestContract
     */
    public static function get($url) : RequestContract
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
     * @return RequestContract
     */
    public static function head($url) : RequestContract
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
     * @return RequestContract
     */
    public static function options($url) : RequestContract
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
     * @return RequestContract
     */
    public static function patch($url) : RequestContract
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
     * @return RequestContract
     */
    public static function post($url) : RequestContract
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
     * @return RequestContract
     */
    public static function put($url) : RequestContract
    {
        return new static('PUT', $url);
    }
}
