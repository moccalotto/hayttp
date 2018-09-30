<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Contracts;

interface Payload
{
    /**
     * Render the body of the payload.
     *
     * @return string
     */
    public function __toString();

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType();
}
