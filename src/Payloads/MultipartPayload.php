<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Payloads;

use Hayttp\Contracts\Payload as PayloadContract;

/**
 * Multipart Body Helper.
 */
class MultipartPayload implements PayloadContract
{
    /**
     * @var string
     */
    protected $boundary;

    /**
     * @var array
     */
    protected $entries = [];

    /**
     * Constructor.
     *
     * @param string $boundary
     */
    public function __construct()
    {
        $this->boundary = '----HayttpBoundary' . mt_rand() . mt_rand();
    }

    /**
     * get the field boundary for this message.
     *
     * @return string
     */
    public function boundary()
    {
        return $this->boundary;
    }

    /**
     * Add a multipart entry.
     *
     * @param string      $name        posted Field name
     * @param string      $data        the data blob to add
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return MultipartPayload
     */
    public function withField($name, $data, $filename, $contentType)
    {
        $clone = clone $this;
        $clone->entries[] = [
            'name' => (string) $name,
            'data' => (string) $data,
            'filename' => $filename ? (string) $filename : null,
            'contentType' => $contentType ? (string) $contentType : null,
        ];

        return $clone;
    }

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType()
    {
        return sprintf('multipart/form-data; boundary=%s', $this->boundary);
    }

    /**
     * Render into a http request body.
     */
    public function render()
    {
        foreach ($this->entries as $entry) {
            $lines[] = '--' . $this->boundary;
            if ($entry['filename']) {
                $lines[] = sprintf('Content-Disposition: form-data; name="%s"; filename="%s"', $entry['name'], $entry['filename']);
            } else {
                $lines[] = sprintf('Content-Disposition: form-data; name="%s"', $entry['name']);
            }

            if ($entry['contentType']) {
                $lines[] = sprintf('Content-Type: %s', $entry['contentType']);
            }

            $lines[] = '';
            $lines[] = $entry['data'];
        }

        $lines[] = '--' . $this->boundary . '--';

        return implode("\r\n", $lines) . "\r\n";
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
