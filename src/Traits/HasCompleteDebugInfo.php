<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits;

/**
 * Add __debugInfo support.
 *
 * Returns ALL variables.
 */
trait HasCompleteDebugInfo
{
    /**
     * Public accessor for all instance variables.
     *
     * @return array
     */
    public function instanceVariables()
    {
        return get_object_vars($this);
    }

    /**
     * Return debug info for var_dump, et al.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return array_merge(
            $this->instanceVariables(),
            method_exists($this, 'extraDebugInfo') ? $this->extraDebugInfo() : []
        );
    }
}
