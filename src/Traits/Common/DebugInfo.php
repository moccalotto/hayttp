<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Common;

/**
 * Add __debugInfo support.
 *
 * Returns ALL variables.
 */
trait DebugInfo
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
        $extraDebugFunc = [$this, 'extraDebugInfo'];
        $extraDebugInfo = is_callable($extraDebugFunc)
            ? $extraDebugFunc()
            : [];

        return array_merge(
            $this->instanceVariables(),
            $extraDebugInfo
        );
    }
}
