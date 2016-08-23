<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

trait HasRequestAccessors
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
     * @return array
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
}
