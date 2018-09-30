<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Traits\Response;

trait StatusHelpers
{
    /**
     * Is the status code 2xx ?
     *
     * @return bool
     */
    public function is2xx()
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is3xx()
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 300 && $statusCode < 400;
    }

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is4xx()
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 400 && $statusCode < 500;
    }

    /**
     * Is the status code 5xx ?
     *
     * @return bool
     */
    public function is5xx()
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 500 && $statusCode < 600;
    }

    /**
     * Is this request a success? (i.e. a 2xx status code).
     *
     * @return bool
     *
     * @see is2xx
     */
    public function isSuccess()
    {
        return $this->is2xx();
    }

    /**
     * Is this request a redirect? (i.e. a 3xx status code).
     *
     * @return bool
     */
    public function isRedirect()
    {
        return $this->is3xx();
    }

    /**
     * Is this request a failure? (i.e. a 4xx or 5xx status code).
     *
     * @return bool
     */
    public function isError()
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 400 && $statusCode < 600;
    }
}
