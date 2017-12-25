<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Request;

use Hayttp\Contracts\Engine as EngineContract;
use Hayttp\Contracts\Payload as PayloadContract;

trait Accessors
{
    /**
     * The http method.
     *
     * @return string
     */
    public function method()
    {
        return $this->method;
    }

    /**
     * The request engine.
     *
     * @return EngineContract
     */
    public function engine()
    {
        return $this->engine;
    }

    /**
     * All registered event hooks.
     *
     * @return array
     */
    public function events()
    {
        return $this->events;
    }

    /**
     * The user agent string.
     *
     * @return string
     */
    public function userAgent()
    {
        return $this->userAgent;
    }

    /**
     * The target url.
     *
     * @return string
     */
    public function url()
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function headers()
    {
        return $this->headers;
    }

    /**
     * Get the contents of a given header.
     *
     * @param string $headerName The name of the header to search for
     *
     * @return string|null the contents of the header or null if it was not found
     */
    public function header($headerName)
    {
        $headerName = strtolower(trim($headerName));

        return isset($this->headers[$headerName])
            ? $this->headers[$headerName]
            : null;
    }

    /**
     * The request payload.
     *
     * @return PayloadContract
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * The proxy to use.
     *
     * @return string|null
     */
    public function proxy()
    {
        return $this->proxy;
    }

    /**
     * Are we doing strict SSL checking?
     *
     * @return bool
     */
    public function secureSsl()
    {
        return $this->secureSsl;
    }

    /**
     * Timeout in seconds.
     *
     * @return float
     */
    public function timeout()
    {
        return $this->timeout;
    }

    /**
     * Cryptographic transport method.
     *
     * @return string
     */
    public function cryptoMethod()
    {
        return $this->cryptoMethod;
    }

    /**
     * The request body.
     *
     * @return string
     */
    public function body()
    {
        return (string) $this->payload;
    }

    /**
     * Get the contents of the Content-Type header.
     *
     * @return string|null
     */
    public function contentType()
    {
        return $this->header('Content-Type');
    }
}
