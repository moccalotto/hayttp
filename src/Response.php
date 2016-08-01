<?php

/*
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp;

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

    public function __construct(string $body, array $headers, RequestContract $request)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->request = $request;
    }

    /**
     * Get the HTTP Response Code.
     */
    public function responseCode()
    {
        if (empty($this->headers)) {
            return;
        }
        list($proto, $status, $message) = preg_split('/\s+/', $this->headers[0]);
    }

    /**
     * Get the headers.
     */
    public function headers() : array
    {
        return $this->headers;
    }

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
