<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Exceptions;

use Exception;
use RuntimeException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * Http connection exception.
 *
 * Thrown when we could not connect to the given URL because of timeout, dns, etc.
 */
class ResponseException extends RuntimeException
{
    /**
     * @var ResponseContract
     */
    protected $response;

    /**
     * Constructor.
     */
    public function __construct(ResponseContract $response, $message, Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct(sprintf('Bad response: %s', $message), 0, $previous);
    }

    /**
     * Get the request that caused the bad response
     *
     * @return RequestContract
     */
    public function getRequest() : RequestContract
    {
        return $this->response->request();
    }

    /**
     * Get the response that couldn't connect.
     *
     * @return ResponseContract
     */
    public function getResponse() : ResponseContract
    {
        return $this->response;
    }
}
