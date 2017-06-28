<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Exceptions\ResponseException;

trait MakesResponseAssertions
{
    /**
     * Throw a ResponseException if $success is false.
     *
     * @param bool $success
     * @param string $message
     */
    protected function ensure($success, $message)
    {
        if (!$success) {
            throw new ResponseException($this, $message);
        }

        return $this;
    }

    public function ensureStatusInRange($min, $max)
    {
        $success = $this->statusCode() >= $min && $this->statusCode() <= $max;

        return $this->ensure(
            $success,
            sprintf(
                'Expected status code to be in range [%d...%d], but %d was returned',
                $min,
                $max,
                $this->statusCode()
            )
        );
    }

    /**
     * Ensure status code is in a given set.
     *
     * @param int[] $array
     *
     * @return $this
     */
    public function ensureStatusIn(array $array)
    {
        return $this->ensure(
            in_array($this->statusCode(), $array),
            sprintf(
                'Expected status code to be one of [%s], but %d was returned',
                implode($array),
                $this->statusCode()
            )
        );
    }

    public function ensureStatus($code)
    {
        return $this->ensure(
            $this->statusCode() == $code,
            sprintf('Expected status code to be %d, but it was %d', $code, $this->statusCode())
        );
    }

    public function ensure2xx()
    {
        return $this->ensureStatusInRange(200, 299);
    }

    public function ensure200()
    {
        return $this->ensureStatus(200);
    }

    public function ensure201()
    {
        return $this->ensureStatus(201);
    }

    public function ensure204()
    {
        return $this->ensureStatus(204);
    }

    public function ensure301()
    {
        return $this->ensureStatus(301);
    }

    public function ensure302()
    {
        return $this->ensureStatus(302);
    }
}
