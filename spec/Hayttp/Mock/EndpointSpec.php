<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace spec\Hayttp\Mock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class EndpointSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            'get|post',
            '{scheme}://{domain}/path',
            function () { return null; }
        );

        $this->shouldHaveType('Hayttp\Mock\Endpoint');
    }

    function it_can_detect_if_it_can_handle_a_request()
    {
        $this->beConstructedWith('get|post', '{anything}/path', function () { return null; });

        $this->handles(hayttp()->get('https://foo.bar/path'))->shouldBe(true);
        $this->handles(hayttp()->get('https://foo.bar/404'))->shouldBe(false);
    }

    function it_can_handle_a_request()
    {
        $handler = function($request, $route) {
            return hayttp()->createMockResponse($request, $route)
                ->withJsonBody($route->params());
        };

        $this->beConstructedWith('get', '{scheme}://{domain}.{tld}/{path1}/{path2}', $handler);

        $response = $this->handle(hayttp()->get('https://foo.bar/baz/bing'));

        $response->shouldHaveType('Hayttp\Mock\MockResponse');

        $response->statusCode()->shouldBe('200');

        $response->contentType()->shouldBe('application/json');
        $response->decoded()->scheme->shouldBe('https');
        $response->decoded()->domain->shouldBe('foo');
        $response->decoded()->tld->shouldBe('bar');
        $response->decoded()->path1->shouldBe('baz');
        $response->decoded()->path2->shouldBe('bing');
    }
}
