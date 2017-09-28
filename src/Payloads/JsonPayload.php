<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Payloads;

use Hayttp\Contracts\Payload as PayloadContract;
use Hayttp\Traits\HasCompleteDebugInfo;

/**
 * Raw (string) body Helper.
 */
class JsonPayload implements PayloadContract
{
    use HasCompleteDebugInfo;

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
    public function __construct($contents, string $contentType = 'application/json')
    {
        $this->contents = $contents;
        $this->contentType = $contentType;
    }

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType() : string
    {
        return $this->contentType;
    }

    /**
     * Render into a http request body.
     */
    public function render() : string
    {
        return json_encode($this->contents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Render the body of the payload.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->render();
    }
}
