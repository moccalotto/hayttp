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

use LogicException;
use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;
use SimpleXmlElement;

/**
 * HTTP Request class.
 */
class Request implements RequestContract
{
    use Traits\HasWithMethods,
        Traits\CreatesRequests,
        Traits\ExpectsCommonMimeTypes,
        Traits\HandlesMultipartPayloads;

    /**
     * @var string
     */
    protected $_method = 'GET';

    /**
     * @var EngineContract
     */
    protected $_engine;

    /**
     * @var array
     */
    protected $_events = [];

    /**
     * @var string
     */
    protected $_userAgent = 'Hayttp';

    /**
     * @var string
     */
    protected $_url;

    /**
     * @var array
     */
    protected $_headers = [];

    /**
     * @var PayloadContract
     */
    protected $_payload;

    /**
     * @var string|null
     */
    protected $_proxy;

    /**
     * @var bool
     */
    protected $_secureSsl = true;

    /**
     * @var float
     */
    protected $_timeout = 5;

    /**
     * @var array
     */
    protected $_cryptoMethod = 'tlsv1.2';

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return RequestContract
     */
    protected function with($property, $value) : RequestContract
    {
        $clone = clone $this;

        $clone->{'_'.$property} = $value;

        return $clone;
    }

    /**
     * Publish an event.
     *
     * @param string $eventName
     * @param array  $args
     */
    protected function publishEvent(string $eventName, array $args = [])
    {
        $events = $this->_events[$eventName] ?? [];

        /** @var callable $event */
        foreach ($events as $event) {
            $event(...$args);
        }
    }

    /**
     * Format headers.
     *
     * Only one hosts header, which must be the first header
     * Only one User Agent header.
     * All other headers are copied verbatim.
     *
     * @return string[]
     */
    public function preparedHeaders() : array
    {
        $headers = $this->_headers;

        $preparedHeaders = [];

        if (isset($headers['Host'])) {
            $preparedHeaders[] = sprintf('Host: %s', $headers['Host']);
            unset($headers['Host']);
        } else {
            $preparedHeaders[] = sprintf('Host: %s', parse_url($this->url, PHP_URL_HOST));
        }

        if (! isset($headers['User-Agent'])) {
            $preparedHeaders[] = sprintf('User-agent: %s', $this->userAgent);
        }

        if (! isset($headers['Content-Type'])) {
            $preparedHeaders[] = sprintf('Content-Type: %s', $this->payload->contentType());
        }

        /** @var string $name, @var string $header */
        foreach ($headers as $name => $header) {
            $preparedHeaders[] = sprintf('%s: %s', $name, $header);
        }

        return $preparedHeaders;
    }

    /**
     * Constructor.
     *
     * @param string $method
     * @param string $url
     */
    public function __construct($method, $url)
    {
        $this->_method = $method;
        $this->_url = $url;
    }

    /**
     * Magic getter.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $candidate = '_'.$name;

        if (property_exists($this, $candidate)) {
            return $this->$candidate;
        }

        if ($name === 'body') {
            return (string) $this->payload;
        }

        if ($name === 'contentLength') {
            return strlen((string) $this->payload);
        }

        throw new LogicException(sprintf(
            'Unknown property "%s"',
            $name
        ));
    }

    /**
     * Setter.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @throws LogicException whenever setting a non-existing property is attempted.
     */
    public function __set($name, $value)
    {
        throw new LogicException(sprintf(
            'Cannot set "%s". This object is immutable',
            $name
        ));
    }

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function render() : string
    {
        $headers = $this->preparedHeaders();

        $parsedUrl = parse_url($this->url);

        $init = sprintf(
            '%s %s%s HTTP/1.0',
            $this->method,
            $parsedUrl['path'] ?? '/',
            $parsedUrl['query'] ?? ''
        );

        $crlf = "\r\n";

        return $init
            .$crlf
            .implode($crlf, $headers)
            .$crlf.$crlf
            .$this->payload;
    }

    /**
     * Return the request as a string.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->render();
    }

    /**
     * Set an event handler to be called just before the message is sent.
     *
     * @param callable $callback. A callable that takes the Request as its only parameter.
     *
     * @return RequestContract
     */
    public function onBeforeSend($callback) : RequestContract
    {
        $events = $this->_events;

        $events['beforeSend'][] = $callback;

        return $this->with('events', $events);
    }

    /**
     * Set an event handler to be called just before the message is sent.
     *
     * @param callable $callback. A callable that takes the Response as its only parameter.
     *
     * @return RequestContract
     */
    public function onAfterResponse($callback) : RequestContract
    {
        $events = $this->events;

        $events['beforeSend'][] = $callback;

        return $this->with('events', $events);
    }

    /**
     * Set the raw payload of the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return RequestContract
     */
    public function sendsRaw(string $payload, string $contentType = 'application/octet-stream') : RequestContract
    {
        if ($this->payload) {
            throw new LogicException('The payload of this request has been locked. You cannot modify it further.');
        }

        return $this->withPayload(new Payloads\RawPayload($payload, $contentType));
    }

    /**
     * Set a JSON payload.
     *
     * @param array|object $payload The payload to send - the payload will always be json encoded.
     *
     * @return RequestContract
     */
    public function sendsJson($payload) : RequestContract
    {
        return $this->sendsRaw(
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'application/json'
        );
    }

    /**
     * Set a XML payload.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return RequestContract
     */
    public function sendsXml($xml) : RequestContract
    {
        if ($xml instanceof SimpleXmlElement) {
            $xml = $xml->asXml();
        }

        return $this->sendsRaw($xml, 'application/xml');
    }

    /**
     * Set a URL-encoded payload.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return RequestContract
     */
    public function sends(array $data) : RequestContract
    {
        return $this->sendsRaw(
            http_build_query($data, '', '&', PHP_QUERY_RFC1738),
            'application/x-www-form-urlencoded'
        );
    }

    /**
     * Add Accept header.
     *
     * @param string $mimeType
     * @param float  $qualityFactor must be between 0 and 1
     *
     * @return RequestContract
     */
    public function expects(string $mimeType, float $qualityFactor = 1) : RequestContract
    {
        // force qualityFactor to be between 0 and 1
        $qualityFactor = max(0, min(1, $qualityFactor));

        return $this->withHeader('Accept', sprintf('%s; %s', $mimeType, $qualityFactor));
    }

    /**
     * Add Accept header with many types.
     *
     * @param array $types Associative array of [mimeType => qualityFactor].
     *
     * @return RequestContract
     */
    public function expectsMany(array $types) : RequestContract
    {
        $parts = [];

        foreach ($types as $mimeType => $qualityFactor) {
            $qualityFactor = max(0, min(1, $qualityFactor));
            $parts[] = sprintf('%s; %s', $mimeType, $qualityFactor);
        }

        return $this->withHeader('Accept', implode(', ', $parts));
    }

    /**
     * Send/execute the request.
     *
     * @return ResponseContract
     *
     * @throws ConnectionException if connection could not be established.
     */
    public function send() : ResponseContract
    {
        $clone = clone $this;

        $clone->publishEvent('beforeSend', [$clone]);

        $engine = $this->_engine ?: new Engines\NativeEngine();

        $response = $engine->send($clone);

        $clone->publishEvent('afterResponse', [$response]);

        return $response;
    }
}
