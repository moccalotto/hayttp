<?php

namespace Moccalotto\Hayttp;

/**
 * Multipart Body Helper.
 */
class MultipartBody
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
        $this->boundary = 'httboundary----' . md5(uniqid('', true));
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
     * @return MultipartBody
     */
    public function withField(string $name, string $data, $filename, $contentType)
    {
        $clone            = clone $this;
        $clone->entries[] = [
            'name' => $name,
            'data' => $data,
            'filename' => $filename,
            'contentType' => $contentType,
        ];

        return $clone;
    }

    /**
     * Render into a http request body.
     */
    public function render() : string
    {
        foreach ($this->entries as $entry) {
            $lines[] = $this->boundary;
            if ($entry['filename']) {
                $lines[] = sprintf('Content-Disposition: name="%s" filename="%s"', $entry['name'], $entry['filename']);
            } else {
                $lines[] = sprintf('Content-Disposition: name="%s"', $entry['name']);
            }

            if ($entry['contentType']) {
                $lines[] = sprintf('Content-Type: %s', $entry['contentType']);
            }

            $lines[] = '';
            $lines[] = $entry['data'];
        }

        $lines[] = $this->boundary;

        return implode("\r\n", $lines);
    }

    public function __toString()
    {
        return $this->render();
    }
}
