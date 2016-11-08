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
    use Traits\HasCallbacks;
    use Traits\HasStatusHelpers;
    use Traits\HasCompleteDebugInfo;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var array
     */
    protected $metadata;

    /**
     * @var RequestContract
     */
    protected $request;

    /**
     * Constructor.
     *
     * @param string          $body     Response body.
     * @param array           $headers  Response headers.
     * @param array           $metadata Engine-specific metadata about the connection.
     * @param RequestContract $request  The request that yielded this response.
     */
    public function __construct(string $body, array $headers, array $metadata, RequestContract $request)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->request = $request;
        $this->metadata = $metadata;
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
     * Get the (raw) metadata.
     *
     * @return array
     */
    public function metadata() : array
    {
        return $this->metadata;
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
     * Get the contents of the Content-Type header.
     *
     * @return string|null
     */
    public function contentType()
    {
        return $this->header('Content-Type');
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
     * Get the contents of a given header.
     *
     * @param string $headerName   The name of the header to search for
     *
     * @return string|null The contents of the header or null if it was not found.
     */
    public function header($headerName)
    {
        $startsWith = $headerName . ':';

        foreach ($this->headers as $header) {
            if (strpos($header, $startsWith) === 0) {
                return trim(explode(':', $header, 2)[1]);
            }
        }
    }

    /**
     * Is this a json response.
     *
     * @return bool
     */
    public function isJson() : bool
    {
        return $this->contentType() === 'application/json';
    }

    /**
     * Is this an xml response.
     *
     * @return bool
     */
    public function isXml() : bool
    {
        return in_array($this->contentType(), [
            'application/xml',
            'text/xml',
        ]);
    }

    /**
     * Is the response text/plain
     *
     * @return bool
     */
    public function isPlainText() : bool
    {
        return $this->contentType() === 'text/plain';
    }

    /**
     * Is this a text response.
     *
     * Is the mime type text/*
     *
     * @return bool
     */
    public function isText() : bool
    {
        return strpos($this->contentType(), 'text/') === 0;
    }

    /**
     * Is this an url-encoded response.
     *
     * @return bool
     */
    public function isUrlEncoded() : bool
    {
        return $this->contentType() === 'application/x-www-form-urlencoded';
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
     * Get the parsed body of the response.
     *
     * If the content type is json, a json object is returned (not an array!)
     *
     * @return mixed
     *      If Content-Type is xml, a SimpleXmlElement is returned.
     *      If Content-Type is json an array or StdClass is returned.
     *      If Content-Type is application/x-www-form-urlencoded, an array is returned.
     *      If Content-Type is text/* The raw response body is returned
     *
     * @throws UnexpectedValueException if the content type could not be determined.
     */
    public function decoded()
    {
        if ($this->isXml()) {
            return $this->xmlDecoded();
        }

        if ($this->isJson()) {
            return $this->jsonDecoded();
        }

        if ($this->isUrlEncoded()) {
            return $this->urlDecoded();
        }

        if ($this->isText()) {
            return $this->body;
        }

        throw new UnexpectedValueException(sprintf(
            'Could not determine the response type. Content-Type: %s',
            $this->contentType() ?: 'unknown'
        ));
    }

    /**
     * Parse the body as json and return it as a PHP value.
     *
     * @return mixed - array or StdClass
     */
    public function jsonDecoded()
    {
        $decoded = json_decode($this->body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf('Response was not valid json: %s', json_last_error_msg()));
        }

        return $decoded;
    }

    /**
     * Parse the body as xml and return it as a SimpleXmlElement.
     *
     * @return SimpleXmlElement
     */
    public function xmlDecoded() : SimpleXmlElement
    {
        try {
            return new SimpleXmlElement($this->body);
        } catch (Exception $e) {
            throw new UnexpectedValueException('Response was not valid xml', 0, $e);
        }
    }

    /**
     * Parse the response as url-encoded and return the parsed array.
     *
     * @return array
     *
     * @see parse_str
     */
    public function urlDecoded() : array
    {
        parse_str($this->body, $result);

        return $result;
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
}
