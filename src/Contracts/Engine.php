<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Contracts;

use Hayttp\Request;
use Hayttp\Response;

interface Engine
{
    /**
     * Send a request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function send(Request $request);
}
