<?php

namespace Moccalotto\Hayttp\Traits;

trait HasStatusHelpers
{
    abstract public function statusCode() : string;

    /**
     * Is the status code 2xx ?
     *
     * @return bool
     */
    public function is2xx() : bool
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 200 && $statusCode < 300;
    }

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is3xx() : bool
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 300 && $statusCode < 400;
    }

    /**
     * Is the status code 3xx ?
     *
     * @return bool
     */
    public function is4xx() : bool
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 400 && $statusCode < 500;
    }

    /**
     * Is the status code 5xx ?
     *
     * @return bool
     */
    public function is5xx() : bool
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
    public function isSuccess() : bool
    {
        return $this->is2xx();
    }

    /**
     * Is this request a redirect? (i.e. a 3xx status code).
     *
     * @return bool
     */
    public function isRedirect() : bool
    {
        return $this->is3xx();
    }

    /**
     * Is this request a failure? (i.e. a 4xx or 5xx status code).
     *
     * @return bool
     */
    public function isError() : bool
    {
        $statusCode = $this->statusCode();

        return $statusCode >= 400 && $statusCode < 600;
    }
}
