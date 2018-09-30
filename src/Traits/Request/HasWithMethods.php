<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Traits\Request;

use Hayttp\Util;
use Hayttp\Request;
use Hayttp\Response;
use SimpleXmlElement;
use Hayttp\Contracts\Engine;
use Hayttp\Contracts\Payload;
use UnexpectedValueException;
use Hayttp\Payloads\RawPayload;
use Hayttp\Payloads\JsonPayload;
use Hayttp\Mock\Endpoint as MockedEndpoint;

trait HasWithMethods
{
    /**
     * Set the timeout.
     *
     * @param float $seconds
     *
     * @return self
     */
    public function withTimeout($seconds)
    {
        return $this->with('timeout', (float) $seconds);
    }

    /**
     * Set the user agent header.
     *
     * @param string $userAgent
     *
     * @return self
     */
    public function withUserAgent($userAgent)
    {
        return $this->with('userAgent', (string) $userAgent);
    }

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string $cryptoMethod
     *
     * @return self
     */
    public function withCryptoMethod($cryptoMethod)
    {
        if (preg_match('/CRYPTO_/A', $cryptoMethod) && defined("static::$cryptoMethod")) {
            return $this->with('cryptoMethod', $cryptoMethod);
        }

        throw new UnexpectedValueException(sprintf(
            'Crypto methed "%s" is invalid. Must be one of [%s]',
            $cryptoMethod,
            Request::CRYPTO_METHODS
        ));
    }

    /**
     * Disable all SSL certificate checks.
     *
     * @return self
     */
    public function skipCertificateChecks()
    {
        return $this->with('secureSsl', false);
    }

    /**
     * Set the transfer engine.
     *
     * @param Engine $engine
     *
     * @return self
     */
    public function withEngine(Engine $engine)
    {
        return $this->with('engine', $engine);
    }

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return self
     */
    public function withHeaders(array $headers)
    {
        return $this->with('headers', Util::normalizeHeaders($headers));
    }

    /**
     * Add an array of headers.
     *
     * @param array $headers
     *
     * @return self
     */
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

    /**
     * Set the proxy server.
     *
     * @param string $proxy URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     *
     * @return self
     */
    public function withProxy($proxy)
    {
        return $this->with('proxy', $proxy);
    }

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function withHeader($name, $value)
    {
        return $this->withAdditionalHeaders([$name => $value]);
    }

    /**
     * Set the TLS version.
     *
     * @param string $version currently, 1.*, 1.0, 1.1 and 1.2 are supported
     *
     * @return self
     */
    public function withTls($version)
    {
        switch ($version) {
            case '1.*':
                return $this->withCryptoMethod(static::CRYPTO_TLS);
            case '1.0':
                return $this->withCryptoMethod(static::CRYPTO_TLS_1_0);
            case '1.1':
                return $this->withCryptoMethod(static::CRYPTO_TLS_1_1);
            case '1.2':
                return $this->withCryptoMethod(static::CRYPTO_TLS_1_2);
            default:
                throw new UnexpectedValueException(sprintf(
                    'TLS version "%s" is unavailable. Must be one of: [1.*, 1.0, 1.1, 1.2]',
                    $version
                ));
        }
    }

    /**
     * Add a basic authorization (which is actually an authenticaation) header.
     *
     * @param string $username
     * @param string $password
     *
     * @return self
     */
    public function withBasicAuth($username, $password)
    {
        return $this->withHeader(
            'Authorization',
            sprintf('Basic %s', base64_encode(sprintf('%s:%s', $username, $password)))
        );
    }

    /**
     * Set the payload of the request.
     *
     * @param Payload $payload
     *
     * @return self
     */
    public function withPayload(Payload $payload)
    {
        return $this->with('payload', $payload);
    }

    /**
     * Execute the $response->$methodName(...$args) as soon as we have a response.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return self
     */
    public function withResponseCall($methodName, array $args = [])
    {
        if (!method_exists(Response::class, $methodName)) {
            throw new UnexpectedValueException(sprintf(
                'Method »%s« does not exist on class %s',
                $methodName,
                Response::class
            ));
        }

        $clone = clone $this;
        $clone->responseCalls[] = [$methodName, $args];

        return $clone;
    }

    /**
     * Set the raw payload of the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return self
     */
    public function withRawPayload($payload, $contentType = 'application/octet-stream')
    {
        return $this->withPayload(new RawPayload($payload, $contentType));
    }

    /**
     * Set a JSON payload.
     *
     * NOTE that the neither unicode characters nor slashes are escaped.
     * This behaviors is compatible with the json standard as well as
     * most browsers' * JSON.stringify() and JSON.parse().
     * However, it is not default output of json_encode.
     *
     * @param array|object $payload     the payload to send - the payload will always be json encoded
     * @param string       $contentType
     *
     * @return self
     */
    public function withJsonPayload($payload, $contentType = 'application/json')
    {
        return $this->withPayload(new JsonPayload($payload, $contentType));
    }

    /**
     * Set an XML payload.
     *
     * @param SimpleXmlElement|string $xml
     * @param string                  $contentType
     *
     * @return self
     */
    public function withXmlPayload($xml, $contentType = 'application/xml')
    {
        if ($xml instanceof SimpleXmlElement) {
            $xml = $xml->asXML();
        }

        return $this->withRawPayload($xml, $contentType);
    }

    /**
     * Set a URL-encoded payload.
     *
     * @param array $form key/value pairs to post as normal urlencoded fields
     *
     * @return self
     */
    public function withFormDataPayload(array $form)
    {
        return $this->withRawPayload(
            http_build_query($form, '', '&', PHP_QUERY_RFC1738),
            'application/x-www-form-urlencoded'
        );
    }

    /**
     * Mock an end point.
     *
     * @param string   $methodPattern
     * @param string   $urlPattern
     * @param callable $handler
     *
     * @return self
     */
    public function withMockedEndpoint($methodPattern, $urlPattern, $handler)
    {
        $mockedEndpoints = $this->mockedEndpoints;

        $mockedEndpoints[] = new MockedEndpoint(
            $methodPattern,
            $urlPattern,
            $handler
        );

        return $this->with('mockedEndpoints', $mockedEndpoints);
    }
}
