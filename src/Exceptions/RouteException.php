<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Exceptions;

use RuntimeException;
use Hayttp\Mock\Route;

/**
 * Http connection exception.
 *
 * Thrown when the response does not adhere to our expectations
 */
class RouteException extends RuntimeException
{
    /**
     * @var Route
     */
    public $route;

    /**
     * Constructor.
     *
     * @param Route      $route
     * @param string     $message
     * @param \Exception $previous
     */
    public function __construct($route, $message, $previous = null)
    {
        $this->route = $route;

        parent::__construct(sprintf('Route exception: %s', $message), 0, $previous);
    }

    /**
     * Get the route.
     *
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }
}
