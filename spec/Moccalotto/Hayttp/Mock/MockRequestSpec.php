<?php

namespace spec\Moccalotto\Hayttp\Mock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Mock\MockResponse;
use Moccalotto\Hayttp\Engines\NativeEngine;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class MockRequestSpec extends ObjectBehavior
{
    function it_is_initializable(Request $request)
    {
        $this->beConstructedWith($request);
        $this->shouldHaveType('Moccalotto\Hayttp\Mock\MockRequest');
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Request');
    }

    function it_can_create_mock_response(Request $request)
    {
        $this->beConstructedWith($request);

        $response = $this->createMockResponse();
        $response->shouldHaveType('Moccalotto\Hayttp\Mock\MockResponse');
        $response->statusCode()->shouldBe('200');
    }

    function it_can_pass_http_messages_through_to_a_real_endpoint(NativeEngine $engine)
    {
        $request = hayttp()->get('https://foo.bar');

        $engine->send(Argument::type(Request::class))
            ->shouldBeCalled()
            ->willReturn(new MockResponse('', ['HTTP/1.0 200 OK'], ['Mocked' => 'true'], $request));
        $this->beConstructedWith($request);

        $this->withEngine($engine)->passthru();
    }
}
