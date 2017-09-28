<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits;

use Hayttp\Contracts\Request as RequestContract;

trait ExpectsCommonMimeTypes
{
    /**
     * Accept application/json.
     *
     * @return RequestContract
     */
    public function expectsJson() : RequestContract
    {
        return $this->expects('application/json');
    }

    /**
     * Accept application/xml.
     */
    public function expectsXml() : RequestContract
    {
        return $this->expects('application/xml');
    }

    /**
     * * Accept * / *.
     *
     * @return RequestContract
     */
    public function expectsAny() : RequestContract
    {
        return $this->expects('*/*');
    }

    /**
     * Expect json response type and throw an exception if json is not returned.
     *
     * @return RequestContract
     */
    public function ensureJson() : RequestContract
    {
        return $this->expectsJson()
            ->withResponseCall('ensureJson');
    }

    /**
     * Expect json response type and throw an exception if json is not returned.
     *
     * @return RequestContract
     */
    public function ensureXml() : RequestContract
    {
        return $this->expectsXml()
            ->withResponseCall('ensureXml');
    }
}
