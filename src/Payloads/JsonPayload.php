<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Payloads;

use InvalidArgumentException;
use Hayttp\Traits\Common\DebugInfo;
use Hayttp\Contracts\Payload as PayloadContract;

/**
 * Raw (string) body Helper.
 */
class JsonPayload implements PayloadContract
{
    use DebugInfo;

    /**
     * @var array|object
     */
    protected $contents;

    /**
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * Constructor.
     *
     * @param array|object $contents
     * @param string       $contentType
     */
    public function __construct($contents, $contentType = 'application/json')
    {
        if (!(is_array($contents) || is_object($contents))) {
            throw new InvalidArgumentException('Contents must be array or object');
        }

        $this->contents = $contents;
        $this->contentType = (string) $contentType;
    }

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType()
    {
        return $this->contentType;
    }

    /**
     * Render into a http request body.
     *
     * @return string The json-encoded contents
     */
    public function render()
    {
        return json_encode($this->contents);
    }

    /**
     * Render the body of the payload.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
}
