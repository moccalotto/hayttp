<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Contracts;

use SimpleXmlElement;

interface Response
{
    /**
     * Constructor.
     *
     * @param string $body
     * @param array $headers
     * @param array $metadata
     * @param Request $request
     */
    public function __construct(string $body, array $headers, array $metadata, Request $request);

    /**
     * Get the request that produced this response.
     *
     * @return RequestContract
     */
    public function request() : Request;


    /**
     * Get the (raw) metadata.
     *
     * @return array.
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
    public function decodedJson();

    /**
     * Parse the body as xml and return it as a SimpleXmlElement.
     *
     * @return SimpleXmlElement
     */
    public function decodedXml() : SimpleXmlElement;

    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string;

    /**
     * Cast to string.
     *
     * @return string
     */
    public function __toString();
}
