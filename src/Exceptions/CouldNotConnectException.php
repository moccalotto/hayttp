<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Exceptions;

use Exception;
use RuntimeException;
use Hayttp\Contracts\Request as RequestContract;

/**
 * Http connection exception.
 *
 * Thrown when we could not connect to the given URL because of timeout, dns, etc.
 */
class CouldNotConnectException extends RuntimeException
{
    /**
     * @var RequestContract
     */
    protected $request;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * Constructor.
     */
    public function __construct(RequestContract $request, array $metadata = [], Exception $previous = null)
    {
        $this->request = $request;
        $this->metadata = $metadata;
        parent::__construct(sprintf(
            'Could not connect to %s',
            $request->url()
        ), 0, $previous);
    }

    /**
     * Get the request that couldn't connect.
     *
     * @return RequestContract
     */
    public function getRequest() : RequestContract
    {
        return $this->request;
    }

    /**
     * Get the metadata for this request.
     *
     * @return array
     */
    public function metadata() : array
    {
        return $this->metadata;
    }
}
