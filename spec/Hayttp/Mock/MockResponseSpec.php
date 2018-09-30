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
use Hayttp\Request;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class MockResponseSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            'body',
            ['HTTP/1.0 200 OK'],
            ['meta' => 'data'],
            hayttp()->get('https://foo.bar')
        );
        $this->shouldHaveType('Hayttp\Mock\MockResponse');
    }

    function it_can_be_modified()
    {
        $this->beConstructedWith(
            '',
            [],
            [], // metadata
            hayttp()->get('https://foo.bar')
        );
        $clone = $this->withStatus(200, 'OK')
            ->withJsonBody(['json' => 'body'])
            ->withHeader('X-foo', 'bar');

        $clone->headers()->shouldBe([
            'HTTP/1.0 200 OK',
            'content-type' => 'application/json',
            'x-foo' => 'bar',
        ]);

        $clone->body()->shouldBe('{"json":"body"}');
    }
}
