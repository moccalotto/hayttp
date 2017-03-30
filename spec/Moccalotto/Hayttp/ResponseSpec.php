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
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/json'];
        $metadata = ['meta' => 'data'];
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->body()->shouldBe($body);
        $this->headers()->shouldBe($headers);
        $this->metadata()->shouldBe($metadata);
        $this->request()->shouldBe($request);
        $this->statusCode()->shouldBe('200');
        $this->reasonPhrase()->shouldBe('OK');
    }

    public function it_decodes_json(Request $request)
    {
        $body = '{"property":"value"}';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/json'];
        $metadata = ['meta' => 'data'];
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->contentType()->shouldBe('application/json');
        $this->decoded()->property->shouldBe('value');
    }

    public function it_decodes_xml(Request $request)
    {
        $body = '<root><child>foo</child></root>';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: application/xml'];
        $metadata = ['meta' => 'data'];
        $this->beConstructedWith($body, $headers, $metadata, $request);

        $this->body()->shouldBe($body);
        $this->headers()->shouldBe($headers);
        $this->metadata()->shouldBe($metadata);
        $this->request()->shouldBe($request);
        $this->statusCode()->shouldBe('200');
        $this->reasonPhrase()->shouldBe('OK');
        $this->contentType()->shouldBe('application/xml');
        $this->decoded()->child->shouldHaveType('SimpleXmlElement');
    }

    public function it_has__apply__callback(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
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

    public function it_has__transform__callback(Request $request)
    {
        $body = '';
        $headers = ['HTTP/1.0 200 OK', 'Content-Type: text/plain'];
        $metadata = ['meta' => 'data'];
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
