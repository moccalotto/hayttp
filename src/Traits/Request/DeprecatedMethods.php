<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Request;

trait DeprecatedMethods
{
    /**
     * Set the raw payload of the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return self
     *
     * @deprecated
     */
    public function sendsRaw($payload, $contentType = 'application/octet-stream')
    {
        return $this->withRawPayload($payload, $contentType);
    }

    /**
     * Set a JSON payload.
     *
     * @deprecated
     *
     * @param array|object $payload the payload to send - the payload will always be json encoded
     *
     * @return self
     */
    public function sendsJson($payload)
    {
        return $this->withJsonPayload($payload);
    }

    /**
     * Set a XML payload.
     *
     * @deprecated
     *
     * @param SimpleXmlElement|string $xml
     *
     * @return self
     */
    public function sendsXml($xml)
    {
        return $this->withXmlPayload($xml);
    }

    /**
     * Set a URL-encoded payload.
     *
     * @deprecated
     *
     * @param array $data key/value pairs to post as normal urlencoded fields
     *
     * @return self
     */
    public function sends(array $data)
    {
        return $this->withFormDataPayload($data);
    }
}
