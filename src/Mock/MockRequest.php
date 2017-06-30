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
use Moccalotto\Hayttp\Util;
use PHPUnit\Framework\Assert as PHPUnit;
use Moccalotto\Hayttp\Request as BaseRequest;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

/**
 * Mock Request.
 *
 * Extends base request, but adds mocking functionality
 */
class MockRequest extends BaseRequest
{
    /**
     * Constructor.
     */
    public function __construct(RequestContract $inner)
    {
        foreach ($inner as $key => $value) {
            $this->$key = $value;
        }

        // a mock request never has further mocks
        $this->mockedEndpoints = [];
    }

    /**
     * Create a new mock response, ready for manipulation.
     *
     * @return MockResponse
     */
    public function createMockResponse()
    {
        return new MockResponse(
            '',
            ['HTTP/1.1 200 OK'],
            ['Mocked-Response' => true],
            $this
        );
    }

    /**
     * Send the request to the real end point and
     * return a MockResponse that can run assertions.
     *
     * @return MockResponse
     */
    public function passthru()
    {
        return MockResponse::createFromBaseResponse($this->send());
    }

    /**
     * We do not allow stacking of mocked end points.
     *
     * @param string   $methodPattern
     * @param string   $urlPattern
     * @param callable $callback
     *
     * @throws LogicException always
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function withMockedEndpoint(string $methodPattern, string $urlPattern, callable $callback)
    {
        throw new LogicException('You cannot nest mocked endpoints');
    }

    /**
     * Assert that the request has a given content type.
     *
     * @param string $expected the expected content type
     *
     * @return $this
     */
    public function assertContentType($expected)
    {
        PHPUnit::assertEquals($expected, $this->contentType(), Util::makePhpUnitExpectationMessage(
            'Invalid content type',
            $expected,
            $this->contentType()
        ));

        return $this;
    }

    /**
     * Assert that the request has a given http method.
     *
     * @param string $expected the expected method
     *
     * @return $this
     */
    public function assertMethod($method)
    {
        $expected = strtoupper($method);
        $actual = strtoupper($this->method);
        PHPUnit::assertEquals($expected, $actual, Util::makePhpUnitExpectationMessage(
            'Invalid method',
            $expected,
            $actual
        ));

        return $this;
    }

    /**
     * Assert that the given header is present on the response.
     */
    public function assertHeader($headerName, $value = null)
    {
        foreach ($this->headers as $key => $val) {
            if (strtolower($headerName) !== strtolower($key)) {
                continue;
            }

            if ($value !== null) {
                PHPUnit::assertEquals($value, $val, Util::makePhpUnitExpectationMessage(
                    "Invalid header $headerName'",
                    $value,
                    $val
                ));
            }

            return $this;
        }

        PHPUnit::assertTrue(false, "Header $headerName not found");
    }

    /**
     * Assert that the response contains the given JSON data.
     */
    public function assertJson(array $data)
    {
        PHPUnit::assertArraySubset(
            $data,
            json_decode($this->payload, true),
            false,
            $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     */
    public function assertJsonFragment(array $data)
    {
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     */
    public function assertJsonMissing(array $data)
    {
    }

    /**
     * Assert that the response contains an exact match of the given JSON data.
     */
    public function assertExactJson(array $data)
    {
    }

    /**
     * Assert that the response has a given JSON structure.
     */
    public function assertJsonStructure(array $structure)
    {
    }
}
