<?php

namespace Moccalotto\Hayttp;

use SimpleXmlElement;
use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Logger as LoggerContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

class Response implements ResponseContract
{
    /**
     * @var string
     */
    protected $body;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var RequestContract
     */
    protected $request;

    /**
     * @var LoggerContract
     */
    protected $logger;

    public function __construct(string $body, array $headers, RequestContract $request, LoggerContract $logger = null)
    {
        $this->body     = $body;
        $this->headers  = $headers;
        $this->request  = $request;
        $this->logger   = $logger;
    }

    /**
     * Get the HTTP Response Code
     */
    public function responseCode()
    {
    }

    public function headers()
    {
        return $this->headers;
    }

    /**
     * Parse the body as json and return it as a PHP value.
     *
     * @return mixed - array or StdClass
     */
    public function decodedJson()
    {
        $decoded = json_decode($this->body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new UnexpectedValueException(sprintf('Could not decode json: %s', json_last_error_msg()));
        }

        return $decoded;
    }

    /**
     * Parse the body as xml and return it as a SimpleXmlElement.
     *
     * @return SimpleXmlElement
     */
    public function decodedXml() : SimpleXmlElement
    {
        return new SimpleXmlElement($this->body);
    }

    public function logger()
    {
        return $this->logger;
    }

    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string
    {
        $crlf = "\r\n";

        return implode($crlf, $this->headers)
            . $crlf
            . $crlf
            . $this->body;
    }

    public function __toString()
    {
        return $this->render();
    }
}
