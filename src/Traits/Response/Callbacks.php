<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Traits\Response;

trait Callbacks
{
    /**
     * Transform this response into something else.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     *
     * @return mixed The result from calling $callbale
     */
    public function transform($callback)
    {
        $this->apply($callback, $outResult);

        return $outResult;
    }

    /**
     * Execute a callback.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     */
    public function apply($callback, &$outResult = null)
    {
        $clone = clone $this;
        $outResult = $callback($clone, $this->request());

        return $clone;
    }

    /**
     * Execute a callback if statusCode is 5xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     */
    public function on5xx($callback, &$outResult = null)
    {
        if (!$this->is5xx()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if statusCode is 4xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     */
    public function on4xx($callback, &$outResult = null)
    {
        if (!$this->is4xx()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if statusCode is 3xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     */
    public function on3xx($callback, &$outResult = null)
    {
        if (!$this->is3xx()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if status code is 2xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     */
    public function on2xx($callback, &$outResult = null)
    {
        if (!$this->is2xx()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if status code is 4xx or 5xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     *
     * @see Request::isError()
     */
    public function onError($callback, &$outResult = null)
    {
        if (!$this->isError()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if status code is 3xx.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     *
     * @see Request::isRedirect()
     */
    public function onRedirect($callback, &$outResult = null)
    {
        if (!$this->isRedirect()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if status code indicates a success.
     *
     * @param callable $callback  Callback with signature: callback($response, $request)
     * @param mixed    $outResult The result of calling $callback
     *
     * @return self A clone of $this
     *
     * @see Request::isSuccess()
     */
    public function onSuccess($callback, &$outResult = null)
    {
        if (!$this->isSuccess()) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }

    /**
     * Execute a callback if status code === $statusCode.
     *
     * @param int      $statusCode
     * @param callable $callback   Callback with signature: callback($response, $request)
     * @param mixed    $outResult  the result form the callback
     *
     * @return self A clone of $this
     */
    public function onStatusCode($statusCode, $callback, &$outResult = null)
    {
        if ($this->statusCode() != $statusCode) {
            return $this;
        }

        return $this->apply($callback, $outResult);
    }
}
