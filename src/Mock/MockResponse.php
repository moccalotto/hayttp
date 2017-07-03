<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Mock;

use SimpleXmlElement;
use Moccalotto\Hayttp\Util;
use Moccalotto\Hayttp\Response as BaseResponse;

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
     */
    public static function fromRequest($request)
    {
        $contentType = $request->headers('accept') ?: 'application/octet-stream';

        return new static(
            '',
            [
                0 => 'HTTP/1.0 200 OK',
                'Content-Type' => $contentType,
            ],
            ['Mock' => 'Empty'],
            $request
        );
    }

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return MockResponse
     */
    protected function with($property, $value) : MockResponse
    {
        $clone = clone $this;

        $clone->$property = $value;

        return $clone;
    }

    public function withStatus($statusCode, $reasonPhrase, $httpVersion = '1.0')
    {
        $clone = clone $this;
        $clone->headers[0] = sprintf('HTTP/%s %d %s', $httpVersion, $statusCode, $reasonPhrase);

        return $clone;
    }

    public function withHeaders(array $headers)
    {
        return $this->with('headers', Util::normalizeHeaders($headers));
    }

    public function withAdditionalHeaders(array $additionalHeaders)
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

    public function withContentType($contentType)
    {
        $headers = $this->headers;

        $headers['content-type'] = $contentType;

        return $this->withHeaders($headers);
    }

    public function withHeader($name, $value)
    {
        return $this->withAdditionalHeaders(
            [$name, $value]
        );
    }

    public function withBody($body) : MockResponse
    {
        return $this->with('body', $body);
    }

    public function withJsonBody($payload)
    {
        return $this->withBody(json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ))->withContentType('application/json');
    }

    public function withXmlBody($xml)
    {
        return $this->withBody(json_encode(
            $xml instanceof SimpleXmlElement ? $xml->asXml() : $xml
        ))->withContentType('application/xml');
    }
}
