<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Mock;

use Hayttp\Exceptions\RouteException;

/**
 * HTTP Mock server.
 */
class Route
{
    /**
     * @var array
     */
    public $params;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct($params)
    {
        $this->params = array_filter($params, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    /**
     * Get a parameter.
     *
     * @param string $paramName
     * @param mixed  $default
     *
     * @return mixed The content of the param with the given key.
     *               If the key does not exist, the default value is returned.
     */
    public function get($paramName, $default = null)
    {
        return $this->params[$paramName] ?? $default;
    }

    /**
     * Does the given parameter exist.
     *
     * @return bool
     */
    public function has($paramName)
    {
        return isset($this->params[$paramName]);
    }

    /**
     * Throw an exception if the given parameter does not exist.
     *
     * @param string $paramName
     *
     * @return $this
     */
    public function ensureHas($paramName)
    {
        if (!$this->has($paramName)) {
            throw new RouteException($this, "Missing route parameter »{$paramName}«");
        }

        return $this;
    }

    /**
     * Get all parameters.
     *
     * @return array
     */
    public function params() : array
    {
        return $this->params;
    }
}
