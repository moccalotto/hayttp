<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

/**
 * Add __debugInfo support.
 *
 * Returns ALL variables.
 */
trait HasCompleteDebugInfo
{
    /**
     * Return debug info for var_dump, et al.
     *
     * @return array
     */
    public function __debugInfo()
    {
        $result = [];
        foreach ($this as $key => $value) {
            $result[$key] = $value;
        }

        return $result;
    }
}
