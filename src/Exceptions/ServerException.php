<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Exceptions;

use RuntimeException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * Http connection exception
 *
 * Thrown when we could not connect to the given URL because of timeout, dns, etc.
 */
class ServerException extends RuntimeException
{
    /**
     * @var ResponseContract
     */
    protected $response;

    /**
     * Constructor
     */
    public function __construct(ResponseContract $response, Exceptions $previous = null)
    {
        $this->response = $response;
        parent::__construct($response->reasonPhrase(), $response->statusCode(), $previous);
    }

    /**
     * Get the request that "caused" the exception.
     */
    public function getRequest() : RequestContract
    {
        return $this->response()->request();
    }

    /**
     * Get the response.
     */
    public function response() : ResponseContract
    {
        return $this->response;
    }
}
