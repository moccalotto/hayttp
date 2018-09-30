<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Traits\Response;

use Hayttp\Util;
use Hayttp\Exceptions\Response as R;
use Hayttp\Exceptions\ResponseException;

trait Assertions
{
    /**
     * Throw a ResponseException if $success is false.
     *
     * @param bool              $success
     * @param ResponseException $exception
     *
     * @return $this
     *
     * @throws ResponseException
     */
    protected function ensure($success, ResponseException $exception)
    {
        if (!$success) {
            throw $exception;
        }

        return $this;
    }

    /**
     * Ensure that status code is in a given range.
     *
     * @param int $min
     * @param int $max
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatusInRange($min, $max)
    {
        $success = $this->statusCode() >= $min && $this->statusCode() <= $max;

        return $this->ensure(
            $success,
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be in range [%d...%d], but %d was returned',
                    $min,
                    $max,
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in a given et of codes.
     *
     * @param int[] $validCodes
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatusIn(array $validCodes)
    {
        return $this->ensure(
            in_array($this->statusCode(), $validCodes),
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be one of [%s], but %d was returned',
                    implode($validCodes),
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code equals $validCode.
     *
     * @param int $validCode
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureStatus($validCode)
    {
        return $this->ensure(
            $this->statusCode() == $validCode,
            new R\StatusCodeException(
                $this,
                sprintf(
                    'Expected status code to be %d, but it was %d',
                    $validCode,
                    $this->statusCode()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in the range [200...299].
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure2xx()
    {
        return $this->ensureStatusInRange(200, 299);
    }

    /**
     * Ensure that the status code is 200.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure200()
    {
        return $this->ensureStatus(200);
    }

    /**
     * Ensure that the status code is 201.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure201()
    {
        return $this->ensureStatus(201);
    }

    /**
     * Ensure that the status code is 204.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure204()
    {
        return $this->ensureStatus(204);
    }

    /**
     * Ensure that the status code is 301.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure301()
    {
        return $this->ensureStatus(301);
    }

    /**
     * Ensure that the status code is 302.
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensure302()
    {
        return $this->ensureStatus(302);
    }

    /**
     * Ensure that the content type is application/json.
     *
     * @param array|object $data
     * @param bool         $strict
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureJson($data = [], $strict = true)
    {
        $this->ensureContentType('application/json');

        if (empty($data)) {
            return $this;
        }

        $bodyArray = Util::recursiveArraySort(json_decode($this->body(), true));
        $dataArray = Util::recursiveArraySort(json_decode(json_encode($data), true));

        if (!is_array($bodyArray)) {
            throw new R\ContentException($this, 'Unparseable json in response body');
        }

        $replaced = array_replace_recursive($bodyArray, $dataArray);

        $exception = new R\ContentException($this, Util::makeExpectationMessage(
            'Could not find data subset in response',
            $dataArray,
            $bodyArray
        ));

        if ($strict && $replaced !== $bodyArray) {
            throw $exception;
        }

        if ($replaced != $bodyArray) {
            throw $exception;
        }

        return $this;
    }

    /**
     * Ensure that the content type is application/xml.
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureXml()
    {
        return $this->ensureContentType(['application/xml', 'text/xml']);
    }

    /**
     * Ensure that the response has a given content type.
     *
     * @param string|string[] $contentType
     *
     * @return $this
     *
     * @throws R\ContentTypeException
     */
    public function ensureContentType($contentType)
    {
        if (is_string($contentType)) {
            $contentType = [$contentType];
        }

        return $this->ensure(
            $this->hasContentType($contentType),
            new R\ContentTypeException(
                $this,
                sprintf(
                    'Expected response content type to be [%s], but it was %s',
                    implode('|', $contentType),
                    $this->contentType()
                )
            )
        );
    }

    /**
     * Ensure that the status code is in the range [200...299].
     *
     * @return $this
     *
     * @throws R\StatusCodeException
     */
    public function ensureSuccess()
    {
        return $this->ensure2xx();
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string $url
     *
     * @return $this
     */
    public function ensureRedirect($url = null)
    {
        return $this->ensureStatusIn([301, 302])
            ->ensureHasHeader('Location', $url);
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed  $expectedValue
     *
     * @return $this
     */
    public function ensureHasHeader($headerName, $expectedValue = null)
    {
        $headerValue = $this->header($headerName);

        if ($headerValue === null) {
            throw new R\HeaderException($this, "Header $headerName is missing");
        }

        if ($expectedValue === null) {
            return $this;
        }

        if ($expectedValue === $headerValue) {
            return $this;
        }

        throw new R\HeaderException(
            $this,
            sprintf(
                'Header %s was expected to have the value %s, but it has the value %s',
                $headerName,
                $expectedValue,
                $headerValue
            )
        );
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param string $value
     *
     * @return $this
     */
    public function ensureContains($value)
    {
        if (strpos($this->body(), $value) === false) {
            throw new R\ContentException(
                $this,
                "Response body was expected to contain $value, but it does not"
            );
        }

        return $this;
    }
}
