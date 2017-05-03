<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp;

use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Engines\CurlEngine;
use Moccalotto\Hayttp\Engines\NativeEngine;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Response;
use PhpSpec\ObjectBehavior;
use SimpleXmlElement;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class RequestSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->beConstructedWith('POST', 'https://example.org');
        $this->shouldHaveType(Request::class);
    }

    public function it_implements_contract()
    {
        $this->beConstructedWith('POST', 'https://example.org');
        $this->shouldHaveType(RequestContract::class);
    }

    public function it_renders_toString()
    {
        $this->beConstructedWith('GET', 'https://example.org');
        $rendered = $this->render();
        $rendered->shouldContain("GET / HTTP/1.0\r\n");
        $rendered->shouldContain("Host: example.org\r\n");
    }

    public function it_posts_json()
    {
        $this->beConstructedThrough('POST', ['https://example.org']);

        $data = ['this' => 'array', 'will' => 'be', 'conterted' => 'to', 'json' => 'object'];

        $request = $this->sendsJson($data);

        $request->shouldHaveType(RequestContract::class);

        $request->render()->shouldContain(
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $request->render()->shouldContain('Content-Type: application/json');
    }

    public function it_posts_xml()
    {
        $this->beConstructedThrough('POST', ['https://example.org']);

        $data = new SimpleXmlElement('<root></root>');

        $request = $this->sendsXml($data);

        $request->shouldHaveType(Request::class);

        $request->render()->shouldContain($data->asXml());

        $request->render()->shouldContain('Content-Type: application/xml');
    }

    public function it_can_send_requests_via_curl(CurlEngine $engine)
    {
        $this->beConstructedThrough('POST', ['https://example.org']);

        $clone = $this->withEngine($engine);

        $response = new Response('Test Body', ['Content-Type: text/plain'], ['meta' => 'data'], $clone->getWrappedObject());

        $engine->send($clone)->shouldBeCalled();
        $engine->send($clone)->willReturn($response);

        $response = $clone->send();

        $response->shouldBe($response);
    }

    public function it_can_send_requests_via_stream(NativeEngine $engine)
    {
        $this->beConstructedThrough('POST', ['https://example.org']);

        $clone = $this->withEngine($engine);

        $response = new Response('Test Body', ['Content-Type: text/plain'], ['meta' => 'data'], $clone->getWrappedObject());

        $engine->send($clone)->shouldBeCalled();
        $engine->send($clone)->willReturn($response);

        $response = $clone->send();

        $response->shouldBe($response);
    }
}
