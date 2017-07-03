<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use LogicException;
use SimpleXmlElement;
use Moccalotto\Hayttp\Util;
use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

class Response implements ResponseContract
{
    use Traits\HasCallbacks;
    use Traits\HasStatusHelpers;
    use Traits\MakesResponseAssertions;
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
     * @param string          $body     response body
     * @param array           $headers  response headers
     * @param array           $metadata engine-specific metadata about the connection
     * @param RequestContract $request  the request that yielded this response
     */
    public function __construct(string $body, array $headers, array $metadata, RequestContract $request)
    {
        $this->body = $body;
        $this->headers = Util::normalizeHeaders($headers);
        $this->request = $request;
        $this->metadata = $metadata;

        foreach ($request->responseCalls() as list($methodName, $args)) {
            $callback = [clone $this, $methodName];
            if (!is_callable($callback)) {
                throw new LogicException(sprintf('Method »%s« does not exist', $methodName));
            }
            call_user_func_array($callback, $args);
        }
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
    public function parseStatusLine() : array
    {
        if (empty($this->headers[0])) {
            throw new LogicException(sprintf(
                'This response has no initial header. Headers found: %s',
                print_r($this->headers, true)
            ));
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
     * Get the content type, but remove anything after the first semi-colon.
     *
     * @return string|null
     */
    public function contentTypeWithoutCharset()
    {
        return explode(';', $this->contentType(), 2)[0];
    }

    /**
     * Get the headers.
     *
     * @return array
     */
    public function headers() : array
    {
        return $this->headers;
    }

    /**
     * Get the contents of a given header.
     *
     * @param string $headerName The name of the header to search for
     *
     * @return string|null the contents of the header or null if it was not found
     */
    public function header($headerName)
    {
        return $this->headers[strtolower(trim($headerName))] ?? null;
    }

    /**
     * Is this a json response.
     *
     * @return bool
     */
    public function isJson() : bool
    {
        return explode(';', $this->contentType())[0] === 'application/json';
    }

    /**
     * Is this an xml response.
     *
     * @return bool
     */
    public function isXml() : bool
    {
        return $this->hasContentType([
            'application/xml',
            'text/xml',
        ]);
    }

    /**
     * Is the response text/plain.
     *
     * @return bool
     */
    public function isPlainText() : bool
    {
        return $this->hasContentType('text/plain');
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
        return $this->hasContentType('application/x-www-form-urlencoded');
    }

    /**
     * Does the response have a given content type.
     *
     * @param string|string[] $contentType
     *
     * @return bool
     */
    public function hasContentType($contentType)
    {
        if (is_array($contentType)) {
            foreach ($contentType as $option) {
                if ($this->hasContentType($option)) {
                    return true;
                }
            }

            return false;
        }

        $expected = explode(';', $contentType);
        $actual   = explode(';', $this->contentType());

        foreach (array_keys($expected) as $idx) {
            if ($expected[$idx] != $actual[$idx]) {
                return false;
            }
        }

        return true;
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
     *               If Content-Type is xml, a SimpleXmlElement is returned.
     *               If Content-Type is json an array or StdClass is returned.
     *               If Content-Type is application/x-www-form-urlencoded, an array is returned.
     *               Otherwise, return the body as a string without decoding it.
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

        return $this->body;
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
        return $this->renderHeaders()
            . "\r\n"
            . $this->body;
    }

    /**
     * Render headers in to a well-formatted string.
     *
     */
    protected function renderHeaders()
    {
        $res = '';

        foreach ($this->headers as $key => $value) {
            if (is_int($key)) {
                $res .= $value;
            } else {
                $key = Util::normalizeHeaderName($key);
                $res .= "$key: {$value}";
            }
            $res .= "\r\n";
        }

        return $res;
    }

    public function extraDebugInfo()
    {
        return [
            'statusCode' => $this->statusCode(),
            'reasonPhrase' => $this->reasonPhrase(),
            'contentType' => $this->contentType(),
            'contentTypeWithoutCharset' => $this->contentTypeWithoutCharset(),
            'location' => $this->header('location'),
            'decoded' => $this->decoded(),
        ];
    }
}
