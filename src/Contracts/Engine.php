<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Contracts;

use Hayttp\Contracts\Request as RequestContract;
use Hayttp\Contracts\Response as ResponseContract;

interface Engine
{
    public function send(RequestContract $request) : ResponseContract;
}
