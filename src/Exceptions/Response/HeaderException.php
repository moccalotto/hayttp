<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Exceptions\Response;

use Hayttp\Exceptions\ResponseException;

/**
 * Http connection exception.
 *
 * Thrown when the response has an invalid content type
 */
class HeaderException extends ResponseException
{
}
