<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Contracts;

use SimpleXmlElement;

/**
 * Http Request.
 */
interface Request
{
    const CRYPTO_ANY = 'any';
    const CRYPTO_SSLV3 = 'sslv3';
    const CRYPTO_TLS = 'tls';
    const CRYPTO_TLS_1_0 = 'tlsv1.0';
    const CRYPTO_TLS_1_1 = 'tlsv1.1';
    const CRYPTO_TLS_1_2 = 'tlsv1.2';

    /**
     * @var array
     */
    const CRYPTO_METHODS = [
        self::CRYPTO_ANY => true,
        self::CRYPTO_SSLV3 => true,
        self::CRYPTO_TLS => true,
        self::CRYPTO_TLS_1_0 => true,
        self::CRYPTO_TLS_1_1 => true,
        self::CRYPTO_TLS_1_2 => true,
    ];

    /**
     * Format headers.
     *
     * Only one hosts header, which must be the first header
     * Only one User Agent header.
     * All other headers are copied verbatim.
     *
     * @return string[]
     */
    public function preparedHeaders() : array;

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function __toString() : string;

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return Request
     */
    public function withCryptoMethod($cryptoMethod) : self;

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return Request
     */
    public function withHeaders(array $headers) : self;

    /**
     * Add an array of headers.
     *
     * @param array $headers
     *
     * @return Request
     */
    public function withAdditionalHeaders(array $headers) : self;

    /**
     * Set the timeout.
     *
     * @param float $seconds
     *
     * @return RequestContract
     */
    public function withTimeout(float $seconds) : self;

    /**
     * Set the proxy server.
     *
     * @param string $proxy URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     *
     * @return Request
     */
    public function withProxy($proxy) : self;

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return Request
     */
    public function withHeader($name, $value) : self;

    /**
     * Disable all SSL certificate checks.
     *
     * @return Request
     */
    public function withInsecureSsl() : self;

    /**
     * Set the transfer engine.
     *
     * @param Engine $engine
     *
     * @return Request
     */
    public function withEngine(Engine $engine) : self;

    /**
     * Set the raw body of the request.
     *
     * @param string $body
     * @param string $contentType
     *
     * @return Request
     */
    public function withRawPayload(string $body, string $contentType = 'application/octet-stream') : self;

    /**
     * Set a JSON payload.
     *
     * @param array|object $body the body to send - the body will be json encoded
     *
     * @return Request
     */
    public function withJsonPayload($body) : self;

    /**
     * Set a XML payload.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return Request
     */
    public function withXmlPayload($xml) : self;

    /**
     * Set a URL-encoded payload.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return Request
     */
    public function withFormDataPayload(array $data) : self;

    /**
     * Set a custom payload
     *
     * @param Payload $payload
     *
     * @return Request
     */
    public function withPayload(Payload $payload) : self;

    /**
     * Add Accept header.
     *
     * @param string $mimeType
     *
     * @return Request
     */
    public function expects(string $mimeType) : self;

    /**
     * Add Accept header with many types.
     *
     * @param array $types associative array of [mimeType => qualityFactor]
     *
     * @return Request
     */
    public function expectsMany(array $types) : self;

    /**
     * Accept application/json.
     *
     * @return Request
     */
    public function expectsJson() : self;

    /**
     * Accept application/xml.
     */
    public function expectsXml() : self;

    /**
     * * Accept * / *.
     *
     * @return Request
     */
    public function expectsAny() : self;

    /**
     * Add a multipart entry.
     *
     * @param string      $name        posted Field name
     * @param string      $data        the data blob to add
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return Request
     */
    public function addMultipartField(
        string $name,
        string $data,
        string $filename = null,
        string $contentType = null
    ) : self;

    /**
     * Add a file to the multipart body.
     *
     * @param string $name        The posted field name
     * @param string $file        The filename on the physical HD
     * @param string $filename    The filename to post. If null, the basename of $filename will be used.
     * @param string $contentType The content type. If null, it will be inferred via mime_content_type($file)
     *
     * @return Request
     */
    public function addFile(string $name, string $file, string $filename = null, string $contentType = null) : self;

    /**
     * Add a data field to the multipart body.
     *
     * @param string      $name        The posted field name
     * @param string      $data        the data blob to add
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return Request
     */
    public function addBlob(string $name, string $data, $contentType = null) : self;

    /**
     * Send/execute the request.
     *
     * @return Response
     */
    public function send() : Response;

    /**
     * The http method.
     *
     * @return string
     */
    public function method() : string;

    /**
     * The request engine.
     *
     * @return Engine
     */
    public function engine() : Engine;

    /**
     * All registered event hooks.
     *
     * @return array
     */
    public function events() : array;

    /**
     * The user agent string.
     *
     * @return string
     */
    public function userAgent() : string;

    /**
     * The target url.
     *
     * @return string
     */
    public function url() : string;

    /**
     * @return array
     */
    public function headers() : array;

    /**
     * The request payload.
     *
     * @return Payload
     */
    public function payload() : Payload;

    /**
     * The proxy to use.
     *
     * @return string|null
     */
    public function proxy();

    /**
     * Are we doing strict SSL checking?
     *
     * @return bool
     */
    public function secureSsl() : bool;

    /**
     * Timeout in seconds.
     *
     * @return float
     */
    public function timeout() : float;

    /**
     * Cryptographic transport method.
     *
     * @return string
     */
    public function cryptoMethod() : string;

    /**
     * Execute the $response->$methodName(...$args) as soon as we have a response.
     *
     * @param string $methodName
     * @param array  $args
     *
     * @return RequestContract
     */
    public function withResponseCall(string $methodName, array $args = []) : self;

    /**
     * Get the calls that are to be executed on the response
     * as soon as we have one.
     *
     * @return array
     */
    public function responseCalls() : array;

    /**
     * The request body.
     *
     * @return string
     */
    public function body() : string;

    /**
     * Get the contents of the Content-Type header.
     *
     * @return string|null
     */
    public function contentType();

    /**
     * Set the raw payload of the request and send/execute the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return Response
     */
    public function sendRaw(string $payload, string $contentType = 'application/octet-stream') : Response;

    /**
     * Set a JSON payload and send/execute the request.
     *
     * @param array|object $payload the payload to send - the payload will always be json encoded
     *
     * @return Response
     */
    public function sendJson($json) : Response;

    /**
     * Set an XML payload and send/execute the request.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return Response
     */
    public function sendXml($xml) : Response;

    /**
     * Set a URL-encoded payload and send/execute the request.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return Response
     */
    public function sendFormData(array $data) : Response;
}
