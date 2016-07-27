<?php

namespace Moccalotto\Hayttp;

use LogicException;
use SimpleXmlElement;
use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * HTTP Request class.
 */
class Request implements RequestContract
{
    use Traits\CreatesRequests;

    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var string
     */
    protected $mode = RequestContract::MODE_RAW;

    /**
     * @var string
     */
    protected $userAgent = 'Hayttp';

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var mixed
     */
    protected $body;

    /**
     * @var bool
     */
    protected $lockedBody = false;

    /**
     * @var Logger
     */
    protected $logging;

    /**
     * @var string|null
     */
    protected $proxy;

    /**
     * @var bool
     */
    protected $followLocation = true;

    /**
     * @var int
     */
    protected $maxRedirects = 10;

    /**
     * @var float
     */
    protected $timeout = 5;

    /**
     * @var string
     */
    protected $multipartBoundary;

    /**
     * @var array
     */
    protected $cryptoMethod = 'tlsv1.2';

    /**
     * @var array
     */
    protected $cryptoMethodMap = [
        RequestContract::CRYPTO_ANY        => STREAM_CRYPTO_METHOD_ANY_CLIENT,
        RequestContract::CRYPTO_SSLV3      => STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
        RequestContract::CRYPTO_TLS        => STREAM_CRYPTO_METHOD_TLS_CLIENT,
        RequestContract::CRYPTO_TLS_1_0    => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT,
        RequestContract::CRYPTO_TLS_1_1    => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT,
        RequestContract::CRYPTO_TLS_1_2    => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    ];

    protected function buildContext(Logger $logger = null)
    {
        $cryptoMethodFlag = $this->cryptoMethodMap[$this->cryptoMethod];

        $options = [
            'http' => [ // http://php.net/manual/en/context.http.php
                'method' => $this->method,
                'user_agent' => $this->userAgent,
                'proxy' => $this->proxy,
                'follow_location' => $this->followLocation,
                'max_redirects' => $this->maxRedirects,
                'timeout' => $this->timeout,
                'protocol_version' => 1.0,
                'ignore_errors' => true,
                'header' => $this->preparedHeaders(),
                'content' => $this->body,
            ],
            'ssl' => [ // http://php.net/manual/en/context.ssl.php
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
                'verify_depth' => 4,
                'crypto_method' => $cryptoMethodFlag,
                // disable compression to prevent CRIME attack.
                // only necessary if an external user can affect
                // the message (cookie, etc.)
                'disable_compression' => true,

            ],
        ];

        if ($logger) {
            $params = ['notification' => [$logger, 'streamNotificationCallback']];
        } else {
            $params = [];
        }

        return stream_context_create($options, $params);
    }

    protected function with($property, $value)
    {
        $clone = clone $this;

        $clone->$property = $value;

        return $clone;
    }

    /**
     * Format headers.
     *
     * Only one hosts header, which must be the first header
     * Only one User Agent header.
     * All other headers are copied verbatim.
     */
    protected function preparedHeaders()
    {
        $userAgentHeader = sprintf('User-agent: %s', $this->userAgent);
        $hostHeader      = sprintf('Host: %s', parse_url($this->url, PHP_URL_HOST));

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

            $preparedHeaders[] = $header;
        }

        array_unshift($preparedHeaders, $hostHeader, $userAgentHeader);

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
        $this->method = $method;
        $this->url    = $url;
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
            . $this->body;
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
        $events = $this->events;

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
     * Publish an event.
     *
     * @param string $eventName
     * @param array  $args
     */
    public function publishEvent(string $eventName, array $args = [])
    {
        $events = $this->events[$eventName] ?? [];

        foreach ($events as $event) {
            $event(...$args);
        }
    }

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
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
     * Set logging true/false
     *
     * @param bool $logging
     *
     * @return RequestContract
     */
    public function withLogging($logging = true) : RequestContract
    {
        return $this->with('logging', $logging);
    }

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return RequestContract
     */
    public function addHeader($name, $value) : RequestContract
    {
        $headers = $this->headers;

        $headers[] = sprintf('%s: %s', $name, $value);

        return $this->withHeaders($headers);
    }

    /**
     * Set the TLS version
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
     * Set the "mode" of the request.
     * Mode can be one of the MODE_* constants
     *  "raw": used for sending raw xml and json
     *  "urlencoded": used for conventional http posts
     *  "multipart": used for file and blob transfers.
     *
     *  @param string $mode
     *
     *  @return RequestContract
     */
    public function withMode($mode) : RequestContract
    {
        return $this->with('mode', $mode);
    }

    /**
     * Set the raw body of the request.
     *
     * @param string $body
     * @param string $contentType
     *
     * @return RequestContract
     */
    public function sendsRaw(string $body, string $contentType = 'application/octet-stream') : RequestContract
    {
        if ($this->lockedBody) {
            throw new LogicException('The body of this request has been locked. You cannot modify it further.');
        }

        return $this->withMode(RequestContract::MODE_RAW)
            ->addHeader('Content-Type', $contentType)
            ->with('lockedBody', true)
            ->with('body', $body);
    }

    /**
     * Set a JSON payload.
     *
     * @param array|object $body The body to send - the body will be json encoded.
     *
     * @return RequestContract
     */
    public function sendsJson($body) : RequestContract
    {
        return $this->sendsRaw(
            json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'application/json'
        );
    }

    /**
     * Set a XML payload.
     *
     * @param SimpleXmlElement $xml
     *
     * @return RequestContract
     */
    public function sendsXml(SimpleXmlElement $xml) : RequestContract
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
    public function addMultipartField(string $name, string $file, string $filename = null, string $contentType = null) : RequestContract
    {
        if ($this->lockedBody && $this->mode !== RequestContract::MODE_MULTIPART) {
            throw new LogicException('The body of this request has been locked. You cannot modify it further.');
        }

        if ($this->body instanceof MultipartBody) {
            $body = $this->body;

            return $this->with('body', $body->withField($name, $file, $filename, $contentType));
        }

        $body    = new MultipartBody();
        $headers = $this->headers;

        return $this
            ->with('lockedBody', true)
            ->withMode(RequestContract::MODE_MULTIPART)
            ->with('body', $body->withField($name, $file, $filename, $contentType))
            ->addHeader('Content-Type', sprintf('multipart/form-data; boundary=%s', $body->boundary()));
    }

    /**
     * Add a file to the multipart body.
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
     * Add a data field to the multipart body.
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
        $clone   = clone $this;
        $logger = $clone->logging ? new Logger() : null;
        $context = $clone->buildContext($logger);

        $clone->lockedBody = true;

        $clone->publishEvent('beforeSend', [$clone]);

        $responseBody = file_get_contents($clone->url, false, $context);

        if ($responseBody === false) {
            $success = false;
            $responseHeaders = [];
        } else {
            $success = true;
            $responseHeaders = $http_response_header;
        }

        $response = new Response(
            $responseBody,
            $responseHeaders,
            $clone,
            $logger
        );

        $clone->publishEvent('afterResponse', [$response]);

        return $response;
    }
}
