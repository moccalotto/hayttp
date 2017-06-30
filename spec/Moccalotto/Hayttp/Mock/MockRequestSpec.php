<?php

namespace spec\Moccalotto\Hayttp\Mock;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Moccalotto\Hayttp\Request;

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
}
