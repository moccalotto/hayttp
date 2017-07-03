<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
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
     * Public accessor for all instance variables.
     *
     * @return array
     */
    public function instanceVariables()
    {
        return get_object_vars($this);
    }

    /**
     * Extra debug info to add.
     *
     * @return array
     */
    public function extraDebugInfo()
    {
        return [];
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
            $this->extraDebugInfo()
        );
    }
}
