<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Traits\Request;

use LogicException;
use Hayttp\Contracts\Payload;
use Hayttp\Payloads\MultipartPayload;

trait Multipart
{
    /**
     * Add a multipart entry.
     *
     * @param string      $name        posted Field name
     * @param string      $data        the data blob to add
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return self
     */
    public function addMultipartField($name, $data, $filename = null, $contentType = null)
    {
        if ($this->payload && !$this->payload instanceof MultipartPayload) {
            throw new LogicException('The payload of this request has been locked. You cannot modify it further.');
        }

        $payload = $this->payload ?: new MultipartPayload();

        return $this->withPayload($payload->withField($name, $data, $filename, $contentType));
    }

    /**
     * Add a file to the multipart payload.
     *
     * @param string $name        The posted field name
     * @param string $file        The filename on the physical HD
     * @param string $filename    The filename to post. If null, the basename of $filename will be used.
     * @param string $contentType The content type of the file.
     *                            If null, the content type will be inferred via mime_content_type()
     *
     * @return self
     */
    public function addFile($name, $file, $filename = null, $contentType = null)
    {
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
     * @param string      $data        the data blob to add
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return self
     */
    public function addBlob($name, $data, $contentType = null)
    {
        return $this->addMultipartField($name, $data, null, $contentType);
    }
}
