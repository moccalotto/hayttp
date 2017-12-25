<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Request;

use Hayttp\Response;
use Hayttp\Engines\NativeEngine;

trait CanSend
{
    /**
     * Send/execute the request.
     *
     * @return Response
     *
     * @throws ConnectionException if connection could not be established
     */
    public function send()
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
     * @return Response
     */
    public function sendRaw($payload, $contentType = 'application/octet-stream')
    {
        return $this->withRawPayload($payload, $contentType)->send();
    }

    /**
     * Set a JSON payload and send/execute the request.
     *
     * @param array|object $payload the payload to send - the payload will always be json encoded
     *
     * @return Response
     */
    public function sendJson($json)
    {
        return $this->withJsonPayload($json)->send();
    }

    /**
     * Set an XML payload and send/execute the request.
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return Response
     */
    public function sendXml($xml)
    {
        return $this->withXmlPayload($xml)->send();
    }

    /**
     * Set a URL-encoded payload and send/execute the request.
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return Response
     */
    public function sendFormData(array $data)
    {
        return $this->withFormDataPayload($data)->send();
    }
}
