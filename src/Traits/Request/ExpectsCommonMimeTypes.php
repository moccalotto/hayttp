<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Request;

trait ExpectsCommonMimeTypes
{
    /**
     * Add Accept header.
     *
     * @param string $mimeType
     *
     * @return self
     */
    public function accept($mimeType)
    {
        return $this->withHeader('Accept', $mimeType);
    }

    /**
     * Add Accept header with many types.
     *
     * @param array $types associative array of [mimeType => qualityFactor]
     *
     * @return self
     */
    public function acceptMany($types)
    {
        $parts = [];

        foreach ($types as $mimeType => $qualityFactor) {
            $qualityFactor = max(0, min(1, $qualityFactor));
            $parts[] = sprintf('%s; q=%s', $mimeType, $qualityFactor);
        }

        return $this->withHeader('Accept', implode(', ', $parts));
    }

    /**
     * Accept application/json.
     *
     * @return self
     */
    public function acceptJson()
    {
        return $this->accept('application/json');
    }

    /**
     * Accept application/xml.
     *
     * @return self
     */
    public function acceptXml()
    {
        return $this->accept('application/xml');
    }

    /**
     * Accept * / *.
     *
     * @return self
     */
    public function acceptAny()
    {
        return $this->accept('*/*');
    }

    /**
     * Expect json response type and throw an exception if json is not returned.
     *
     * @return self
     */
    public function ensureJson()
    {
        return $this->acceptJson()
            ->withResponseCall('ensureJson');
    }

    /**
     * Expect json response type and throw an exception if json is not returned.
     *
     * @return self
     */
    public function ensureXml()
    {
        return $this->acceptXml()
            ->withResponseCall('ensureXml');
    }
}
