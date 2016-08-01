<?php

namespace Moccalotto\Hayttp;

use LogicException;
use SimpleXmlElement;
use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * HTTP Request class.
 *
 */
class Request implements RequestContract
{
    use Traits\CreatesRequests;

    /**
     * Available crypto methods.
     *
     * @var array
     */
    protected $cryptoMethodMap = [
        RequestContract::CRYPTO_ANY => true,
        RequestContract::CRYPTO_SSLV3 => true,
        RequestContract::CRYPTO_TLS => true,
        RequestContract::CRYPTO_TLS_1_0 => true,
        RequestContract::CRYPTO_TLS_1_1 => true,
        RequestContract::CRYPTO_TLS_1_2 => true,
    ];

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
    protected $_headers = ['Expect:'];

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

    protected function with($property, $value)
    {
        $clone = clone $this;

        $clone->{'_' . $property} = $value;

        return $clone;
    }

    /**
     * Format headers.
     *
     * Only one hosts header, which must be the first header
     * Only one User Agent header.
     * All other headers are copied verbatim.
     */
    public function preparedHeaders()
    {
        $userAgentHeader   = sprintf('User-agent: %s', $this->userAgent);
        $hostHeader        = sprintf('Host: %s', parse_url($this->url, PHP_URL_HOST));
        $contentTypeHeader = sprintf('Content-Type: %s', $this->payload->contentType());

        $preparedHeaders = [];

        foreach ($this->headers as $header) {
            if (preg_match('/user-agent:/Ai', $header)) {
                $userAgentHeader = $header;
                continue;
            }

            if (preg_match('/host:/Ai', $header)) {
                $hostHeader = $header;
                continue;
            }

            if (preg_match('/content-type:/Ai', $header)) {
                $contentTypeHeader = $header;
            }

            $preparedHeaders[] = $header;
        }

        array_unshift(
            $preparedHeaders,
            $hostHeader,
            $userAgentHeader,
            $contentTypeHeader
        );

        return $preparedHeaders;
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

        foreach ($events as $event) {
            $event(...$args);
        }
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
        $this->_url    = $url;
    }

    public function __get($name)
    {
        $candidate = '_' . $name;

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
            . $crlf
            . implode($crlf, $headers)
            . $crlf . $crlf
            . $this->payload;
    }

    public function __toString()
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
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return RequestContract
     */
    public function withCryptoMethod($cryptoMethod)
    {
        if (!isset($this->cryptoMethodMap[$cryptoMethod])) {
            throw new UnexpectedValueException(sprintf(
                'Crypto methed "%s" is invalid. Must be one of [%s]',
                $cryptoMethod,
                implode(', ', array_keys($this->cryptoMethodMap))
            ));
        }

        return $this->with('cryptoMethod', $cryptoMethod);
    }

    /**
     * Set the transfer engine.
     *
     * @param EngineContract $engine
     *
     * @return RequestContract
     */
    public function withEngine(EngineContract $engine)
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
        return $this->with('headers', $headers);
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
        $headers = $this->headers;

        $headers[] = sprintf('%s: %s', $name, $value);

        return $this->withHeaders($headers);
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
    public function withBasicAuth(string $username, string $password): RequestContract
    {
        return $this->withHeader(sprintf(
            'Authorization: Basic %s',
            base64_encode(sprintf('%s:%s', $username, $password))
        ));
    }

    public function withPayload(PayloadContract $payload)
    {
        return $this->with('payload', $payload);
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
     * Add a multipart entry.
     *
     * @param string      $name        posted Field name
     * @param string      $data        The data blob to add.
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return RequestContract
     */
    public function addMultipartField(string $name, string $data, string $filename = null, string $contentType = null) : RequestContract
    {
        if ($this->payload && !$this->payload instanceof Payloads\MultipartPayload) {
            throw new LogicException('The payload of this request has been locked. You cannot modify it further.');
        }

        $payload = $this->payload ?: new Payloads\MultipartPayload();

        return $this->withPayload($payload->withField($name, $data, $filename, $contentType));
    }

    /**
     * Add a file to the multipart payload.
     *
     * @param string $name        The posted field name
     * @param string $file        The filename on the physical HD
     * @param string $filename    The filename to post. If null, the basename of $filename will be used.
     * @param string $contentType The content type of the file. If null, the content type will be inferred via mime_content_type()
     *
     * @return RequestContract
     */
    public function addFile(
        string $name,
        string $file,
        string $filename = null,
        string $contentType = null
    ) : RequestContract {
        if ($filename === null) {
            $filename = basename($file);
        }

        if ($contentType === null) {
            $contentType = mime_content_type($file);
        }

        return $this->addMultipartField($name, file_get_contents($file), $filename, $contentType);
    }

    /**
     * Add a data field to the multipart payload.
     *
     * @param string      $name        The posted field name
     * @param string      $data        The data blob to add.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return RequestContract
     */
    public function addBlob(string $name, string $data, $contentType = null) : RequestContract
    {
        return $this->addMultipartField($name, $data, null, $contentType);
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
