<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Contracts;

use SimpleXmlElement;

interface Response
{
    /**
     * Constructor.
     *
     * @param string  $body
     * @param array   $headers
     * @param array   $metadata
     * @param Request $request
     */
    public function __construct(string $body, array $headers, array $metadata, Request $request);

    /**
     * Cast to string.
     *
     * @return string
     */
    public function __toString();

    /**
     * Get the request that produced this response.
     *
     * @return RequestContract
     */
    public function request() : Request;

    /**
     * Get the (raw) metadata.
     *
     * @return array
     */
    public function metadata() : array;

    /**
     * Get the HTTP Response Code.
     *
     * @return string
     */
    public function statusCode() : string;

    /**
     * Get the http reason phrase.
     *
     * @return string
     */
    public function reasonPhrase() : string;

    /**
     * Get the headers.
     *
     * @return string[]
     */
    public function headers() : array;

    /**
     * Get the response body.
     *
     * @return string
     */
    public function body() : string;

    /**
     * Parse the body as json and return it as a PHP value.
     *
     * @return mixed - array or StdClass
     */
    public function jsonDecoded();

    /**
     * Parse the body as xml and return it as a SimpleXmlElement.
     *
     * @return SimpleXmlElement
     */
    public function xmlDecoded() : SimpleXmlElement;

    /**
     * Parse the response as url-encoded and return the parsed array.
     *
     * @return array
     *
     * @see parse_str
     */
    public function urlDecoded() : array;

    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string;

    /**
     * Transform this response into something else.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     *
     * @return mixed The result from calling $callbale
     */
    public function transform(callable $callback);

    /**
     * Execute a callback.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     */
    public function apply(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if statusCode is 5xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     */
    public function on5xx(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if statusCode is 4xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     */
    public function on4xx(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if statusCode is 3xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     */
    public function on3xx(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if status code is 2xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     */
    public function on2xx(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if status code is 4xx or 5xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     *
     * @see RequestContract::isError()
     */
    public function onError(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if status code indicates a success.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return Response A clone of $this
     *
     * @see RequestContract::isSuccess()
     */
    public function onSuccess(callable $callback, &$result = null) : Response;

    /**
     * Execute a callback if status code === $statusCode.
     *
     * @param int statusCode
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   the result form the callback
     *
     * @return Response A clone of $this
     */
    public function onStatusCode(int $statusCode, callable $callback, &$result = null) : Response;

    /**
     * Is the status code 2xx ?
     *
     * @return bool
     */
    public function is2xx() : bool;

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is3xx() : bool;

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is4xx() : bool;

    /**
     * Is the status code 5xx ?
     *
     * @return bool
     */
    public function is5xx() : bool;

    /**
     * Is this request a success? (i.e. a 2xx status code).
     *
     * @return bool
     *
     * @see is2xx
     */
    public function isSuccess() : bool;

    /**
     * Is this request a redirect? (i.e. a 3xx status code).
     *
     * @return bool
     */
    public function isRedirect() : bool;

    /**
     * Is this request a failure? (i.e. a 4xx or 5xx status code).
     *
     * @return bool
     */
    public function isError() : bool;

    /**
     * Is this a json response.
     *
     * @return bool
     */
    public function isJson() : bool;

    /**
     * Is this an xml response.
     *
     * @return bool
     */
    public function isXml() : bool;

    /**
     * Is the response text/plain.
     *
     * @return bool
     */
    public function isPlainText() : bool;

    /**
     * Is this a text response.
     *
     * Is the mime type text/*
     *
     * @return bool
     */
    public function isText() : bool;

    /**
     * Is this an url-encoded response.
     *
     * @return bool
     */
    public function isUrlEncoded() : bool;

    /**
     * Ensure that status code is in a given range.
     *
     * @param int $min
     * @param int $max
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensureStatusInRange($min, $max) : Response;

    /**
     * Ensure that the status code is in a given et of codes.
     *
     * @param int[] $validCodes
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensureStatusIn(array $validCodes) : Response;

    /**
     * Ensure that the status code equals $validCode
     *
     * @param int $validCode
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensureStatus($validCode);

    /**
     * Ensure that the status code is in the range [200...299]
     *
     * @param int $validCode
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure2xx();

    /**
     * Ensure that the status code is 200
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure200();

    /**
     * Ensure that the status code is 201
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure201();

    /**
     * Ensure that the status code is 204
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure204();

    /**
     * Ensure that the status code is 301
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure301();

    /**
     * Ensure that the status code is 302
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensure302();

    /**
     * Ensure that the content type is application/json
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensureJson();

    /**
     * Ensure that the content type is application/xml
     *
     * @return Response
     *
     * @throws ResponseException
     */
    public function ensureXml();
}
