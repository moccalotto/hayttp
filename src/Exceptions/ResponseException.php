<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Exceptions;

use Exception;
use Hayttp\Request;
use Hayttp\Response;
use RuntimeException;

/**
 * Http connection exception.
 *
 * Thrown when the response does not adhere to our expectations
 */
class ResponseException extends RuntimeException
{
    /**
     * @var Response
     */
    protected $response;

    /**
     * Constructor.
     */
    public function __construct(Response $response, $message, Exception $previous = null)
    {
        $this->response = $response;
        parent::__construct(sprintf('Bad response: %s', $message), 0, $previous);
    }

    /**
     * Get the request that caused the bad response.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->response->request();
    }

    /**
     * Get the response that couldn't connect.
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }
}
