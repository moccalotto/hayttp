<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp;

use Moccalotto\Hayttp\Request;
use PhpSpec\ObjectBehavior;

/**
 * Test.
 *
 * @codingStandardsIgnoreStart
 */
class ResponseSpec extends ObjectBehavior
{
    public function it_is_initializable(Request $request)
    {
        $this->beConstructedWith('body', [], [], $request);
        $this->shouldHaveType('Moccalotto\Hayttp\Response');
    }

    public function it_implements_contract(Request $request)
    {
        $this->beConstructedWith('body', [], [], $request);
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Response');
    }

    public function it_has_accessors(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->body()->shouldBe($body);
        $this->headers()->shouldBe($headers);
        $this->metadata()->shouldBe($metadata);
        $this->request()->shouldBe($request);
        $this->statusCode()->shouldBe('200');
        $this->reasonPhrase()->shouldBe('OK');
        $this->contentType()->shouldBe('application/json');
        $this->jsonDecoded()->property->shouldBe('value');
    }

    public function it_renders(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $rawRequest = <<<EOF
HTTP/1.0 200 OK\r
Content-Type: application/json\r
\r
{"property":"value"}
EOF;
        $this->render()->shouldBe($rawRequest);
    }
}
