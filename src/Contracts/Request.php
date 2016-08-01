<?php

/*
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Contracts;

use SimpleXmlElement;

/**
 * Http Request.
 *
 * @property Payload $payload
 * @property float $timeout
 * @property string $body Rendered payload/body
 * @property string $contentLength Length of rendered body
 * @property string $cryptoMethod
 * @property string $engine
 * @property string $headers
 * @property string $methed
 * @property string $proxy
 * @property string $secureSsl
 * @property string $url
 * @property string $userAgent
 */
interface Request
{
    const CRYPTO_ANY = 'any';
    const CRYPTO_SSLV3 = 'sslv3';
    const CRYPTO_TLS = 'tls';
    const CRYPTO_TLS_1_0 = 'tlsv1.0';
    const CRYPTO_TLS_1_1 = 'tlsv1.1';
    const CRYPTO_TLS_1_2 = 'tlsv1.2';

    /// @var array
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
     * Set an event handler to be called just before the message is sent.
     *
     * @param callable $callback. A callable that takes the Request as its only parameter.
     *
     * @return Request
     */
    public function onBeforeSend($callback) : Request;

    /**
     * Set an event handler to be called just before the message is sent.
     *
     * @param callable $callback. A callable that takes the Response as its only parameter.
     *
     * @return Request
     */
    public function onAfterResponse($callback) : Request;

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return Request
     */
    public function withCryptoMethod($cryptoMethod) : Request;

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return Request
     */
    public function withHeaders(array $headers) : Request;

    /**
     * Set the proxy server.
     *
     * @param string $proxy URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     *
     * @return Request
     */
    public function withProxy($proxy) : Request;

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return Request
     */
    public function withHeader($name, $value) : Request;

    /**
     * Disable all SSL certificate checks.
     *
     * @return Request
     */
    public function withInsecureSsl() : Request;

    /**
     * Set the transfer engine.
     *
     * @param Engine $engine
     *
     * @return Request
     */
    public function withEngine(Engine $engine) : Request;

    /**
     * Set the raw body of the request.
     *
     * @param string $body
     * @param string $contentType
     *
     * @return Request
     */
    public function sendsRaw(string $body, string $contentType = 'application/octet-stream') : Request;

    /**
     * Set a JSON payload.
     *
     * @param array|object $body The body to send - the body will be json encoded.
     *
     * @return Request
     */
    public function sendsJson($body) : Request;

    /**
     * Set a XML payload.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return Request
     */
    public function sendsXml($xml) : Request;

    /**
     * Set a URL-encoded payload.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return Request
     */
    public function sends(array $data) : Request;

    /**
     * Add Accept header.
     *
     * @param string $mimeType
     * @param float  $qualityFactor must be between 0 and 1
     *
     * @return Request
     */
    public function expects(string $mimeType, float $qualityFactor = 1) : Request;

    /**
     * Add Accept header with many types.
     *
     * @param array $types Associative array of [mimeType => qualityFactor].
     *
     * @return Request
     */
    public function expectsMany(array $types) : Request;

    /**
     * Accept application/json.
     *
     * @return Request
     */
    public function expectsJson() : Request;

    /**
     * Accept application/xml.
     */
    public function expectsXml() : Request;

    /**
     * * Accept * / *.
     *
     * @return Request
     */
    public function expectsAny() : Request;

    /**
     * Add a multipart entry.
     *
     * @param string      $name        posted Field name
     * @param string      $data        The data blob to add.
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return Request
     */
    public function addMultipartField(
        string $name,
        string $file,
        string $filename = null,
        string $contentType = null
    ) : Request;

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
    public function addFile(string $name, string $file, string $filename = null, string $contentType = null) : Request;

    /**
     * Add a data field to the multipart body.
     *
     * @param string      $name        The posted field name
     * @param string      $data        The data blob to add.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return Request
     */
    public function addBlob(string $name, string $data, $contentType = null) : Request;

    /**
     * Send/execute the request.
     *
     * @return Response
     */
    public function send() : Response;
}
