<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Mock;

use Closure;
use Hayttp\Util;
use LogicException;
use Hayttp\Request;
use Hayttp\Response;

/**
 * Mock Endpoint.
 *
 * Simulates a HTTP server that can handle a given set of
 * methods and urls.
 */
class Endpoint
{
    /**
     * @var string
     */
    public $methodRegex;

    /**
     * @var string
     */
    public $urlRegex;

    /**
     * @var Closure
     */
    public $handler;

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
        $this->handler = Util::closureFromCallable($handler);
    }

    /**
     * Convert a url pattern into a regular expression.
     *
     * @param string $urlPattern A url patterl like http://{domain}.{tld}/foo/{id}
     *
     * @return string
     */
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
     * @param Request $request
     *
     * @return bool
     */
    public function handles(Request $request)
    {
        return preg_match($this->methodRegex, $request->method())
            && preg_match($this->urlRegex, $request->url());
    }

    /**
     * Handle/mock a request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        preg_match($this->urlRegex, $request->url(), $matches);

        $response = call_user_func($this->handler, clone $request, new Route($matches));

        if (!($response instanceof Response)) {
            throw new LogicException(sprintf(
                'The handler must return an instance of %s',
                MockResponse::class
            ));
        }

        return $response;
    }
}
