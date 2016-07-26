<?php

namespace Moccalotto\Hayttp\Contracts;

use SimpleXmlElement;

interface Request
{
    const MODE_MULTIPART = 'multipart';
    const MODE_RAW       = 'raw';

    const CRYPTO_ANY = 'any';
    const CRYPTO_SSLV3 = 'sslv3';
    const CRYPTO_TLS   = 'tls';
    const CRYPTO_TLS_1_0 = 'tlsv1.0';
    const CRYPTO_TLS_1_1 = 'tlsv1.1';
    const CRYPTO_TLS_1_2 = 'tlsv1.2';

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function render() : string;

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
     * Publish an event.
     *
     * @param string $eventName
     * @param array  $args
     */
    public function publishEvent(string $eventName, array $args = []);

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
     * Set logging true/false
     *
     * @param bool $logging
     *
     * @return RequestContract
     */
    public function withLogging($logging = true) : Request;

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return Request
     */
    public function addHeader($name, $value) : Request;

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
     * @param SimpleXmlElement $xml
     *
     * @return Request
     */
    public function sendsXml(SimpleXmlElement $xml) : Request;

    /**
     * Set a URL-encoded payload.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return Request
     */
    public function sends(array $data) : Request;

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
