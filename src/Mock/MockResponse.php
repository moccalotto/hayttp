<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Mock;

use Hayttp\Util;
use Hayttp\Request;
use SimpleXmlElement;
use Hayttp\Response as BaseResponse;

/**
 * Mock Request.
 *
 * Extends base request, but adds mocking functionality
 */
class MockResponse extends BaseResponse
{
    /**
     * Factory.
     *
     * Create an empty response from a given request.
     *
     * @param Request $request
     * @param Route   $route   Routing of parameters passed to the handler
     *
     * @return self
     */
    public static function new(Request $request, Route $route)
    {
        $contentType = $request->header('accept') ?: 'application/octet-stream';

        return new static(
            '',
            [
                0 => 'HTTP/1.0 200 OK',
                'Content-Type' => $contentType,
            ],
            [
                'X-Mock' => 'true',
                'route' => $route,
            ],
            $request
        );
    }

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return self
     */
    protected function with($property, $value)
    {
        $clone = clone $this;
        $property = (string) $property;

        $clone->$property = $value;

        return $clone;
    }

    /**
     * Set the status code and reason phrase of the http response.
     *
     * @param int    $statusCode
     * @param string $reasonPhrase
     * @param string $httpVersion
     *
     * @return self
     */
    public function withStatus($statusCode, $reasonPhrase, $httpVersion = '1.0')
    {
        $clone = clone $this;
        $clone->headers[0] = sprintf('HTTP/%s %d %s', $httpVersion, $statusCode, $reasonPhrase);

        return $clone;
    }

    /**
     * Set headers on the mock response.
     *
     * @param array $headers
     *
     * @return self
     */
    public function withHeaders($headers)
    {
        return $this->with('headers', Util::normalizeHeaders($headers));
    }

    /**
     * Add headers to the mock response.
     *
     * @param array $additionalHeaders
     *
     * @return self
     */
    public function withAdditionalHeaders($additionalHeaders)
    {
        $res = $this->headers;
        $additionalHeaders = Util::normalizeHeaders($additionalHeaders);

        foreach ($additionalHeaders as $key => $value) {
            if (isset($res[$key])) {
                $res[$key] .= ';' . $value;
            } else {
                $res[$key] = $value;
            }
        }

        return $this->withHeaders($res);
    }

    /**
     * Set the content type.
     *
     * @param string $contentType
     *
     * @return self
     */
    public function withContentType($contentType)
    {
        $headers = $this->headers;

        $headers['content-type'] = (string) $contentType;

        return $this->withHeaders($headers);
    }

    /**
     * Add a header to the response.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function withHeader($name, $value)
    {
        return $this->withAdditionalHeaders(
            [(string) $name => (string) $value]
        );
    }

    /**
     * Set the body of the response.
     *
     * @param string $body
     *
     * @return self
     */
    public function withBody($body)
    {
        return $this->with('body', $body);
    }

    /**
     * Set the a json body.
     *
     * @param array|object $payload
     *
     * @return self
     */
    public function withJsonBody($payload)
    {
        return $this->withBody(Util::toJson($payload))
            ->withContentType('application/json');
    }

    /**
     * Set the a json body.
     *
     * @param string|SimpleXmlElement $payload
     *
     * @return self
     */
    public function withXmlBody($xml)
    {
        return $this->withBody(
            $xml instanceof SimpleXmlElement ? $xml->asXML() : $xml
        )->withContentType('application/xml');
    }

    /**
     * Add information about the route to the response.
     *
     * @param Route $route
     *
     * @return self
     */
    public function withRoute($route)
    {
        $metadata = $this->metadata;
        $metadata['route'] = $route;

        return $this->with('metadata', $metadata);
    }
}
