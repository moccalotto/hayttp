<?php

namespace Moccalotto\Hayttp\Traits;

use Moccalotto\Hayttp\Contracts\Response as ResponseContract;
use Moccalotto\Hayttp\Contracts\Requestas as RequestContract;

trait HasCallbacks
{
    abstract public function statusCode() : string;

    abstract public function isRedirect() : bool;

    abstract public function isSuccess() : bool;

    abstract public function isError() : bool;

    abstract public function is2xx() : bool;

    abstract public function is3xx() : bool;

    abstract public function is4xx() : bool;

    abstract public function is5xx() : bool;

    /**
     * Transform this response into something else.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     *
     * @return mixed The result from calling $callbale
     */
    public function transform(callable $callback)
    {
        $this->apply($callback, $result);

        return $result;
    }

    /**
     * Execute a callback.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     */
    public function apply(callable $callback, &$result = null) : ResponseContract
    {
        $clone = clone $this;
        $result = $callback($clone, $this->request());

        return $clone;
    }

    /**
     * Execute a callback if statusCode is 5xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     */
    public function on5xx(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->is5xx()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if statusCode is 4xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     */
    public function on4xx(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->is4xx()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if statusCode is 3xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     */
    public function on3xx(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->is3xx()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if status code is 2xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     */
    public function on2xx(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->is2xx()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if status code is 4xx or 5xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     *
     * @see RequestContract::isError()
     */
    public function onError(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->isError()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if status code is 3xx.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     *
     * @see RequestContract::isRedirect()
     */
    public function onRedirect(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->isRedirect()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if status code indicates a success.
     *
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   The result of calling $callback
     *
     * @return ResponseContract A clone of $this
     *
     * @see RequestContract::isSuccess()
     */
    public function onSuccess(callable $callback, &$result = null) : ResponseContract
    {
        if (!$this->isSuccess()) {
            return $this;
        }

        return $this->apply($callback, $result);
    }

    /**
     * Execute a callback if status code === $statusCode.
     *
     * @param int statusCode
     * @param callable $callback Callback with signature: callback($response, $request)
     * @param mixed    $result   the result form the callback
     *
     * @return ResponseContract A clone of $this
     */
    public function onStatusCode(int $statusCode, callable $callback, &$result = null) : ResponseContract
    {
        if ($this->statusCode() !== $statusCode) {
            return $this;
        }

        return $this->apply($callback, $result);
    }
}
