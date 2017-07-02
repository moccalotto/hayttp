<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Exceptions\Response;

use Moccalotto\Hayttp\Exceptions\ResponseException;

/**
 * Http connection exception.
 *
 * Thrown when the response has an invalid content type
 */
class ContentTypeException extends ResponseException
{
}
