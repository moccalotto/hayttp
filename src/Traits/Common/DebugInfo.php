<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Common;

use Hayttp\Util;
use UnexpectedValueException;

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
        $extraDebugInfo = method_exists($this, 'extraDebugInfo') && is_callable([$this, 'extraDebugInfo'])
            ? call_user_func([$this, 'extraDebugInfo'])
            : [];

        if (!is_array($extraDebugInfo)) {
            throw new UnexpectedValueException(sprintf(
                'Result of calling %s::extraDebugInfo() must be an array!. The actual return type was %s',
                get_class($this),
                gettype($extraDebugInfo)
            ));
        }

        return array_merge($this->instanceVariables(), $extraDebugInfo);
    }
}
