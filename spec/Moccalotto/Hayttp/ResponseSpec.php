<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace spec\Moccalotto\Hayttp;

use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Util;
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
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith('body', [], [], $request);
        $this->shouldHaveType('Moccalotto\Hayttp\Response');
    }

    public function it_implements_contract(Request $request)
    {
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith('body', [], [], $request);
        $this->shouldHaveType('Moccalotto\Hayttp\Contracts\Response');
    }

    public function it_has_accessors(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/json'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->body()->shouldBe($body);
        $this->metadata()->shouldBe($metadata);
        $this->request()->shouldBe($request);
        $this->statusCode()->shouldBe('200');
        $this->reasonPhrase()->shouldBe('OK');
        $this->headers()->shouldBe([
            0 => 'HTTP/1.0 200 OK',
            'content-type' => 'application/json',
        ]);
    }

    public function it_decodes_json(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/json'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->contentType()->shouldBe('application/json');
        $this->decoded()->property->shouldBe('value');
    }

    public function it_detects_content_type_without_charset(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/json;charset=UTF-8'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->contentTypeWithoutCharset()->shouldBe('application/json');
        $this->decoded()->property->shouldBe('value');
    }

    public function it_decodes_xml(Request $request)
    {
        $body = '<root><child>foo</child></root>';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/xml'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->body()->shouldBe($body);
        $this->headers()->shouldBe(Util::normalizeHeaders($headers));
        $this->metadata()->shouldBe($metadata);
        $this->request()->shouldBe($request);
        $this->statusCode()->shouldBe('200');
        $this->reasonPhrase()->shouldBe('OK');
        $this->contentType()->shouldBe('application/xml');
        $this->decoded()->child->shouldHaveType('SimpleXmlElement');
    }

    public function it_has_APPLY_callback(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        // Since we cannot test via the $out parameter - we hax it a bit
        // and use a referenced scope variable.
        $scopeVar = [];

        $result = $this->apply(function ($res, $req) use (&$scopeVar) {
            $scopeVar = [$res, $req];
        }/*,  $out */); // cannot check out param - phpspec enters permanent loop.

        $result->shouldBe($scopeVar[0]);

        // Ensure that we return a clone of the result.
        $result->shouldNotBe($this->getWrappedObject());
    }

    public function it_has_TRANSFORM_callback(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $result = $this->transform(function ($response, $request) {
            return [$response, $request];
        });

        $result[0]->shouldHaveType('Moccalotto\Hayttp\Contracts\Response');
        $result[1]->shouldHaveType('Moccalotto\Hayttp\Contracts\Request');

        $result[0]->request()->shouldBe($result[1]);
    }

    public function it_has_2xx_success_callbacks(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $scopeVar = [];
        $called = function ($res, $req) use (&$scopeVar) {
            $scopeVar[] = [$res, $req];
        };
        $notCalled = function ($req, $res) use (&$scopeVar) {
            throw new \Exception('Will not be executed');
        };

        $this->on2xx($called)
        ->on3xx($notCalled)
        ->on4xx($notCalled)
        ->on5xx($notCalled)
        ->onSuccess($called)
        ->onRedirect($notCalled)
        ->onError($notCalled);

        foreach ($scopeVar as list($res, $req)) {
            $this->body()->shouldBe($res->body());
            $this->headers()->shouldBe($res->headers());
            $this->metadata()->shouldBe($res->metadata());
        }
    }

    public function it_has_3xx_redirect_callbacks(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 302 Found', 'Content-Type: text/plain', 'Location: https://example.org'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $scopeVar = [];
        $called = function ($res, $req) use (&$scopeVar) {
            $scopeVar[] = [$res, $req];
        };
        $notCalled = function ($req, $res) use (&$scopeVar) {
            throw new \Exception('Will not be executed');
        };

        $this->on2xx($notCalled)
        ->on3xx($called)
        ->on4xx($notCalled)
        ->on5xx($notCalled)
        ->onSuccess($notCalled)
        ->onRedirect($called)
        ->onError($notCalled);

        foreach ($scopeVar as list($res, $req)) {
            $this->body()->shouldBe($res->body());
            $this->headers()->shouldBe($res->headers());
            $this->metadata()->shouldBe($res->metadata());
        }
    }

    public function it_has_4xx_error_callbacks(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 400 Bad Request', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $scopeVar = [];
        $called = function ($res, $req) use (&$scopeVar) {
            $scopeVar[] = [$res, $req];
        };
        $notCalled = function ($req, $res) use (&$scopeVar) {
            throw new \Exception('Will not be executed');
        };
        $this->on2xx($notCalled)
        ->on3xx($notCalled)
        ->on4xx($called)
        ->on5xx($notCalled)
        ->onSuccess($notCalled)
        ->onRedirect($notCalled)
        ->onError($called);

        foreach ($scopeVar as list($res, $req)) {
            $this->body()->shouldBe($res->body());
            $this->headers()->shouldBe($res->headers());
            $this->metadata()->shouldBe($res->metadata());
        }
    }

    public function it_has_5xx_error_callbacks(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 500 Internal Server Error', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $scopeVar = [];
        $called = function ($res, $req) use (&$scopeVar) {
            $scopeVar[] = [$res, $req];
        };
        $notCalled = function ($req, $res) use (&$scopeVar) {
            throw new \Exception('Will not be executed');
        };
        $this->on2xx($notCalled)
        ->on3xx($notCalled)
        ->on4xx($notCalled)
        ->on5xx($called)
        ->onSuccess($notCalled)
        ->onRedirect($notCalled)
        ->onError($called);

        foreach ($scopeVar as list($res, $req)) {
            $this->body()->shouldBe($res->body());
            $this->headers()->shouldBe($res->headers());
            $this->metadata()->shouldBe($res->metadata());
        }
    }

    public function it_can_be_rendered_to_raw_text(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $rawRequest = <<<EOF
HTTP/1.0 200 OK\r
Content-Type: application/json\r
\r
{"property":"value"}
EOF;
        $this->render()->shouldBe($rawRequest);
    }

    public function it_executes_deferred_calls(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([
            ['ensure301', []],
        ]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->shouldThrow('\Moccalotto\Hayttp\Exceptions\ResponseException')->duringInstantiation();
    }

    public function it_throws_exception_in_case_of_invalid_deferred_call(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([
            ['foo', []],
        ]);
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->shouldThrow('LogicException')->duringInstantiation();
    }

    public function it_can_assert_if_body_contains_a_json_blob(Request $request)
    {
        $body = <<<JSON
{
    "foo": "bar",
    "baz": {
        "bing": 1234,
        "test": 5678
    }
}
JSON;
        $headers = [
            'HTTP/1.0 200 OK',
            'Content-Type: application/json',
        ];
        $metadata = ['meta' => 'data'];
        $request->responseCalls()->willReturn([]);

        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->ensureJson()->shouldBe($this->getWrappedObject());

        $this->ensureJson([
            'foo' => 'bar',
        ])->shouldBe($this->getWrappedObject());

        $this->ensureJson([
            'foo' => 'bar',
        ])->shouldBe($this->getWrappedObject());

        $this->ensureJson([
            'baz' => []
        ])->shouldBe($this->getWrappedObject());

        $this->ensureJson([
            'baz' => [
                "bing" => 1234
            ]
        ])->shouldBe($this->getWrappedObject());

        $this->ensureJson(
            [
                'baz' => [
                    "bing" => '1234'
                ]
            ],
            false
        )->shouldBe($this->getWrappedObject());

        $this->shouldThrow('Moccalotto\Hayttp\Exceptions\Response\ContentException')->during(
            'ensureJson',
            [
                [
                    'foo' => 'baz',
                ]
            ]
        );
        $this->shouldThrow('Moccalotto\Hayttp\Exceptions\Response\ContentException')->during(
            'ensureJson',
            [
                [
                    'baz' => [
                        'bing' => '1234',
                    ],
                ],
                true
            ]
        );
    }

}
