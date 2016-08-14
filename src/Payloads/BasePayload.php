<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Payloads;

use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;

abstract class BasePayload implements PayloadContract
{
    /**
     * Render the body of the payload.
     *
     * @return string
     */
    abstract public function __toString() : string;

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    abstract public function contentType() : string;

    /**
     * Array of headers to add to the request upon sending.
     *
     * @return string[]
     */
    public function addedHeaders() : array
    {
        return [];
    }

    /**
     * Add these strings to the path.
     *
     * @return string[]
     */
    public function addedPath() : array
    {
        return [];
    }

    /**
     * Add these args to the query string.
     *
     * @return array Associative array of args to add.
     */
    public function addedQueryArgs() : array
    {
        return [];
    }
}
