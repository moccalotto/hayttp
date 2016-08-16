<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Payloads;

use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;

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
    protected $entries;

    /**
     * Constructor.
     *
     * @param string $boundary
     */
    public function __construct()
    {
        $this->boundary = '----HayttpBoundary'.substr(md5(uniqid()), 0, 10);
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
     * @param string      $data        The data blob to add.
     * @param string|null $filename    The filename to use. If null, no filename is sent.
     * @param string|null $contentType The content type to send. If null, no content-type will be sent.
     *
     * @return MultipartPayload
     */
    public function withField(string $name, string $data, $filename, $contentType) : MultipartPayload
    {
        $clone = clone $this;
        $clone->entries[] = [
            'name' => $name,
            'data' => $data,
            'filename' => $filename,
            'contentType' => $contentType,
        ];

        return $clone;
    }

    /**
     * The Content-Type header to use when sending this payload.
     *
     * @return string
     */
    public function contentType() : string
    {
        return sprintf('multipart/form-data; boundary=%s', $this->boundary);
    }

    /**
     * Render into a http request body.
     */
    public function render() : string
    {
        foreach ($this->entries as $entry) {
            $lines[] = '--'.$this->boundary;
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

        $lines[] = '--'.$this->boundary.'--';

        return implode("\r\n", $lines)."\r\n";
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
