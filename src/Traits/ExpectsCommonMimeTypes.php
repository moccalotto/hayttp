<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Contracts\Request as RequestContract;

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
}
