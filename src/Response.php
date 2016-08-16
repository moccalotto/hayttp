<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use LogicException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;
use SimpleXmlElement;
use UnexpectedValueException;

class Response implements ResponseContract
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var RequestContract
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param string $body
     * @param array $headers
     * @param RequestContract $request
     */
    public function __construct(string $body, array $headers, RequestContract $request)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->request = $request;
    }

    /**
     * Return the status line of the response.
     *
     * @return string[]
     *
     * @throws LogicException
     */
    protected function parseStatusLine() : array
    {
        if (empty($this->headers)) {
            throw new LogicException('This response has no headers');
        }

        return preg_split('/\s+/', $this->headers[0]);
    }

    /**
     * Get the request that produced this response.
     *
     * @return RequestContract
     */
    public function request() : RequestContract
    {
        return $this->request;
    }

    /**
     * Get the HTTP Response Code.
     *
     * @return string
     */
    public function statusCode() : string
    {
        return $this->parseStatusLine()[1];
    }

    /**
     * Get the http reason phrase.
     *
     * @return string
     */
    public function reasonPhrase() : string
    {
        return $this->parseStatusLine()[2];
    }

    /**
     * Get the headers.
     *
     * @return string[]
     */
    public function headers() : array
    {
        return $this->headers;
    }

    /**
     * Get the response body.
     *
     * @return string
     */
    public function body() : string
    {
        return $this->body;
    }

    /**
     * Parse the body as json and return it as a PHP value.
     *
     * @return mixed - array or StdClass
     */
    public function decodedJson()
    {
        $decoded = json_decode($this->body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf('Could not decode json: %s', json_last_error_msg()));
        }

        return $decoded;
    }

    /**
     * Parse the body as xml and return it as a SimpleXmlElement.
     *
     * @return SimpleXmlElement
     */
    public function decodedXml() : SimpleXmlElement
    {
        return new SimpleXmlElement($this->body);
    }

    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string
    {
        $crlf = "\r\n";

        return implode($crlf, $this->headers)
            .$crlf
            .$crlf
            .$this->body;
    }

    /**
     * Cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
