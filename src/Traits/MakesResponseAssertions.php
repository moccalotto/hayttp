<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Exceptions\ResponseException;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

trait MakesResponseAssertions
{
    /**
     * Throw a ResponseException if $success is false.
     *
     * @param bool $success
     * @param string $message
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    protected function ensure($success, $message) : ResponseContract
    {
        if (!$success) {
            throw new ResponseException($this, $message);
        }

        return $this;
    }

    /**
     * Ensure that status code is in a given range.
     *
     * @param int $min
     * @param int $max
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensureStatusInRange($min, $max) : ResponseContract
    {
        $success = $this->statusCode() >= $min && $this->statusCode() <= $max;

        return $this->ensure(
            $success,
            sprintf(
                'Expected status code to be in range [%d...%d], but %d was returned',
                $min,
                $max,
                $this->statusCode()
            )
        );
    }

    /**
     * Ensure that the status code is in a given et of codes.
     *
     * @param int[] $validCodes
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensureStatusIn(array $validCodes) : ResponseContract
    {
        return $this->ensure(
            in_array($this->statusCode(), $validCodes),
            sprintf(
                'Expected status code to be one of [%s], but %d was returned',
                implode($validCodes),
                $this->statusCode()
            )
        );
    }

    /**
     * Ensure that the status code equals $validCode
     *
     * @param int $validCode
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensureStatus($validCode)
    {
        return $this->ensure(
            $this->statusCode() == $validCode,
            sprintf('Expected status code to be %d, but it was %d', $validCode, $this->statusCode())
        );
    }

    /**
     * Ensure that the status code is in the range [200...299]
     *
     * @param int $validCode
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure2xx()
    {
        return $this->ensureStatusInRange(200, 299);
    }

    /**
     * Ensure that the status code is 200
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure200()
    {
        return $this->ensureStatus(200);
    }

    /**
     * Ensure that the status code is 201
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure201()
    {
        return $this->ensureStatus(201);
    }

    /**
     * Ensure that the status code is 204
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure204()
    {
        return $this->ensureStatus(204);
    }

    /**
     * Ensure that the status code is 301
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure301()
    {
        return $this->ensureStatus(301);
    }

    /**
     * Ensure that the status code is 302
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensure302()
    {
        return $this->ensureStatus(302);
    }

    /**
     * Ensure that the content type is application/json
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensureJson()
    {
        return $this->ensure(
            $this->isJson(),
            sprintf('Expected response type to be application/json, but it was %s', $this->contentType())
        );
    }

    /**
     * Ensure that the content type is application/xml
     *
     * @return ResponseContract
     *
     * @throws ResponseException
     */
    public function ensureXml()
    {
        return $this->ensure(
            $this->isXml(),
            sprintf('Expected response type to be application/json, but it was %s', $this->contentType())
        );
    }
}
