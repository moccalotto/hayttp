<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits;

use Hayttp\Engines\NativeEngine;
use Hayttp\Contracts\Response as ResponseContract;

trait SendsRequest
{
    /**
     * Send/execute the request.
     *
     * @return ResponseContract
     *
     * @throws ConnectionException if connection could not be established
     */
    public function send() : ResponseContract
    {
        $clone = clone $this;

        $clone->mockedEndpoints = [];
        $clone->engine = $clone->engine ?: new NativeEngine();

        foreach ($this->mockedEndpoints as $endpoint) {
            if ($endpoint->handles($clone)) {
                return $endpoint->handle($clone);
            }
        }

        return $clone->engine->send($clone);
    }

    /**
     * Set the raw payload of the request and send/execute the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return ResponseContract
     */
    public function sendRaw(string $payload, string $contentType = 'application/octet-stream') : ResponseContract
    {
        return $this->withRawPayload($payload, $contentType)->send();
    }

    /**
     * Set a JSON payload and send/execute the request.
     *
     * @param array|object $payload the payload to send - the payload will always be json encoded
     *
     * @return ResponseContract
     */
    public function sendJson($json) : ResponseContract
    {
        return $this->withJsonPayload($json)->send();
    }

    /**
     * Set an XML payload and send/execute the request.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return ResponseContract
     */
    public function sendXml($xml) : ResponseContract
    {
        return $this->withXmlPayload($xml)->send();
    }

    /**
     * Set a URL-encoded payload and send/execute the request.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return ResponseContract
     */
    public function sendFormData(array $data) : ResponseContract
    {
        return $this->withFormDataPayload($data)->send();
    }
}
