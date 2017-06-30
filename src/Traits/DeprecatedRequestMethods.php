<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Contracts\Request as RequestContract;

trait DeprecatedRequestMethods
{
    /**
     * Set the raw payload of the request.
     *
     * @param string $payload
     * @param string $contentType
     *
     * @return RequestContract
     *
     * @deprecated
     */
    public function sendsRaw(string $payload, string $contentType = 'application/octet-stream') : RequestContract
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
     * @return RequestContract
     */
    public function sendsJson($payload) : RequestContract
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
     * @return RequestContract
     */
    public function sendsXml($xml) : RequestContract
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
     * @return RequestContract
     */
    public function sends(array $data) : RequestContract
    {
        return $this->withFormDataPayload($data);
    }
}
