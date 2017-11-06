<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits;

use SimpleXmlElement;
use Hayttp\Util;
use UnexpectedValueException;
use Hayttp\Payloads\RawPayload;
use Hayttp\Payloads\JsonPayload;
use Hayttp\Contracts\Engine as EngineContract;
use Hayttp\Contracts\Payload as PayloadContract;
use Hayttp\Contracts\Request as RequestContract;
use Hayttp\Contracts\Response as ResponseContract;

trait HasWithMethods
{
    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return RequestContract
     */
    abstract protected function with($property, $value) : RequestContract;

    /**
     * Set the timeout.
     *
     * @param float $seconds
     *
     * @return RequestContract
     */
    public function withTimeout(float $seconds) : RequestContract
    {
        return $this->with('timeout', $seconds);
    }

    /**
     * Set the user agent header.
     *
     * @param string $userAgent
     *
     * @return RequestContract
     */
    public function withUserAgent(string $userAgent) : RequestContract
    {
        return $this->with('userAgent', $userAgent);
    }

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return RequestContract
     */
    public function withCryptoMethod($cryptoMethod) : RequestContract
    {
        if (!isset(RequestContract::CRYPTO_METHODS[$cryptoMethod])) {
            throw new UnexpectedValueException(sprintf(
                'Crypto methed "%s" is invalid. Must be one of [%s]',
                $cryptoMethod,
                implode(', ', array_keys(RequestContract::CRYPTO_METHODS))
            ));
        }

        return $this->with('cryptoMethod', $cryptoMethod);
    }

    /**
     * Disable all SSL certificate checks.
     *
     * @return RequestContract
     */
    public function withInsecureSsl() : RequestContract
    {
        return $this->with('secureSsl', false);
    }

    /**
     * Set the transfer engine.
     *
     * @param EngineContract $engine
     *
     * @return RequestContract
     */
    public function withEngine(EngineContract $engine) : RequestContract
    {
        return $this->with('engine', $engine);
    }

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return RequestContract
     */
    public function withHeaders(array $headers) : RequestContract
    {
        return $this->with('headers', Util::normalizeHeaders($headers));
    }

    /**
     * Add an array of headers.
     *
     * @param array $headers
     *
     * @return RequestContract
     */
    public function withAdditionalHeaders(array $additionalHeaders) : RequestContract
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
     * @return RequestContract
     */
    public function withProxy($proxy) : RequestContract
    {
        return $this->with('proxy', $proxy);
    }

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return RequestContract
     */
    public function withHeader($name, $value) : RequestContract
    {
        return $this->withAdditionalHeaders([$name => $value]);
    }

    /**
     * Set the TLS version.
     *
     * @param string $version currently, 1.*, 1.0, 1.1 and 1.2 are supported
     *
     * @return RequestContract
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
     * @return RequestContract
     */
    public function withBasicAuth(string $username, string $password) : RequestContract
    {
        return $this->withHeader(
            'Authorization',
            sprintf('Basic %s', base64_encode(sprintf('%s:%s', $username, $password)))
        );
    }

    /**
     * Set the payload of the request.
     *
     * @param PayloadContract $payload
     *
     * @return RequestContract
     */
    public function withPayload(PayloadContract $payload)
    {
        return $this->with('payload', $payload);
    }

    /**
     * Execute the $response->$methodName(...$args) as soon as we have a response.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return RequestContract
     */
    public function withResponseCall(string $methodName, array $args = []) : RequestContract
    {
        if (!method_exists(ResponseContract::class, $methodName)) {
            throw new UnexpectedValueException(sprintf(
                'Method »%s« does not exist on class %s',
                $methodName,
                ResponseContract::class
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
     * @return RequestContract
     */
    public function withRawPayload(string $payload, string $contentType = 'application/octet-stream') : RequestContract
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
     * @return RequestContract
     */
    public function withJsonPayload($payload, $contentType = 'application/json') : RequestContract
    {
        return $this->withPayload(new JsonPayload($payload, $contentType));
    }

    /**
     * Set an XML payload.
     *
     * @param SimpleXmlElement|string $xml
     * @param string                  $contentType
     *
     * @return RequestContract
     */
    public function withXmlPayload($xml, $contentType = 'application/xml') : RequestContract
    {
        if ($xml instanceof SimpleXmlElement) {
            $xml = $xml->asXml();
        }

        return $this->withRawPayload($xml, $contentType);
    }

    /**
     * Set a URL-encoded payload.
     *
     * @param array $form key/value pairs to post as normal urlencoded fields
     *
     * @return RequestContract
     */
    public function withFormDataPayload(array $form) : RequestContract
    {
        return $this->withRawPayload(
            http_build_query($form, '', '&', PHP_QUERY_RFC1738),
            'application/x-www-form-urlencoded'
        );
    }
}
