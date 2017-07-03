<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Mock;

use LogicException;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * HTTP Mock server.
 */
class Endpoint
{
    /**
     * Constructor.
     *
     * @param string   $methodPattern
     * @param string   $urlPattern
     * @param callable $handler
     */
    public function __construct($methodPattern, $urlPattern, $handler)
    {
        $urlRegex = $this->makeUrlRegex($urlPattern);

        $this->methodRegex = "/^($methodPattern)$/i";
        $this->urlRegex = "#^{$urlRegex}$#i";
        $this->handler = $handler;
    }

    protected function makeUrlRegex($urlPattern)
    {
        return preg_replace(
            '/{([a-z0-9_-]+?)}/',
            '(?P<$1>.+?)',
            addcslashes($urlPattern, '.[]()-')
        );
    }

    /**
     * Does this mock endpoint handle a given request ?
     *
     * @param RequestContract $request
     *
     * @return bool
     */
    public function handles(RequestContract $request) : bool
    {
        return preg_match($this->methodRegex, $request->method())
            && preg_match($this->urlRegex, $request->url());
    }

    /**
     * Handle/mock a request.
     *
     * @param RequestContract $request
     *
     * @return ResponseContract
     */
    public function handle(RequestContract $request) : ResponseContract
    {
        preg_match($this->urlRegex, $request->url(), $matches);

        $response = call_user_func($this->handler, clone $request, new Route($matches));

        if (!($response instanceof ResponseContract)) {
            throw new LogicException(sprintf(
                'The handler must return an instance of %s',
                MockResponse::class
            ));
        }

        return $response;
    }
}
