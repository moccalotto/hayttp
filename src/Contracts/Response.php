<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Contracts;

interface Response
{
    /**
     * Get the entire response, including headers, as a string.
     *
     * @return string
     */
    public function render() : string;
}
