<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Payloads;

use Hayttp\Traits\Common\DebugInfo;
use Hayttp\Contracts\Payload as PayloadContract;

/**
 * Raw (string) body Helper.
 */
class RawPayload implements PayloadContract
{
    use DebugInfo;

    /**
     * @var string
     */
    protected $contents;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * Constructor.
     *
     * @param string $contents
     * @param string $contentType
     */
    public function __construct($contents, $contentType)
    {
        $this->contents = (string) $contents;
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
     */
    public function render()
    {
        return $this->contents;
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
