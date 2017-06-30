<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp\Mock;

use SimpleXmlElement;
use Moccalotto\Hayttp\Util;
use PHPUnit\Framework\Assert as PHPUnit;
use Moccalotto\Hayttp\Response as BaseResponse;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;

/**
 * Mock Request.
 *
 * Extends base request, but adds mocking functionality
 */
class MockResponse extends BaseResponse
{
    /**
     * Factory.
     *
     * @param ResponseContract $response
     *
     * @return MockResponse
     */
    public static function createFromBaseResponse(ResponseContract $response)
    {
        return new static(
            $response->body(),
            $response->headers(),
            $response->metadata(),
            $response->request()
        );
    }

    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return MockResponse
     */
    protected function with($property, $value) : MockResponse
    {
        $clone = clone $this;

        $clone->$property = $value;

        return $clone;
    }

    public function withStatus($statusCode, $reasonPhrase, $httpVersion = '1.0')
    {
        $clone = clone $this;
        $clone->headers[0] = sprintf('HTTP/%s %d %s', $httpVersion, $statusCode, $reasonPhrase);

        return $clone;
    }

    public function withHeaders(array $headers) : MockResponse
    {
        $tmp = [];

        foreach ($headers as $key => $val) {
            if (is_int($key) && strpos($val, ':') === false) {
                $tmp[] = $val;
                continue;
            }

            if (is_int($key)) {
                list($key, $val) = explode(':', $val, 2);
            }

            $key = strtolower($key);
            $val = ltrim($val);
            $tmp[] = sprintf('%s: %s', $key, $val);
        }

        return $this->with('headers', $tmp);
    }

    public function withHeader($name, $value)
    {
        $headers = $this->headers;
        $headers[strtolower($name)] = $value;

        return $this->withHeaders($headers);
    }

    public function withBody($body) : MockResponse
    {
        return $this->with('body', $body);
    }

    public function withJsonBody($payload)
    {
        return $this->withBody(json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ))->withHeader('Content-Type', 'application/json');
    }

    public function withXmlBody($xml)
    {
        return $this->withBody(json_encode(
            $xml instanceof SimpleXmlElement ? $xml->asXml() : $xml
        ))->withHeader('Content-Type', 'application/xml');
    }

    /**
     * Assert that the response has a successful status code.
     *
     * @return $this
     */
    public function assertSuccessful()
    {
        $statusCode = $this->statusCode();

        PHPUnit::assertTrue(
            $this->isSuccessful(),
            "Response status code [$statusCode] is not a successful status code."
        );

        return $this;
    }

    /**
     * Assert that the response has the given status code.
     *
     * @param int $status
     *
     * @return $this
     */
    public function assertStatus($status)
    {
        $actual = $this->statusCode();

        PHPUnit::assertTrue(
            $actual === $status,
            "Expected status code {$status} but received {$actual}."
        );

        return $this;
    }

    /**
     * Assert whether the response is redirecting to a given URI.
     *
     * @param string $url
     *
     * @return $this
     */
    public function assertRedirect($url = null)
    {
        $statusCode = $this->statusCode();

        PHPUnit::assertTrue(
            $this->isRedirect(),
            "Response status code [$statusCode] is not a valid redirect"
        );

        if (!is_null($url)) {
            PHPUnit::assertEquals($url, $this->header('Location'));
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given header and equals the optional value.
     *
     * @param string $headerName
     * @param mixed  $value
     *
     * @return $this
     */
    public function assertHeader($headerName, $value = null)
    {
        PHPUnit::assertTrue(
            $this->headers->has($headerName),
            "Header [{$headerName}] not present on response."
        );

        $actual = $this->header($headerName);

        if (!is_null($value)) {
            PHPUnit::assertEquals(
                $value,
                $this->header($headerName),
                "Header [{$headerName}] was found, but value [{$actual}] does not match [{$value}]."
            );
        }

        return $this;
    }

    /**
     * Asserts that the response contains the given cookie and equals the optional value.
     *
     * @param string $cookieName
     * @param mixed  $value
     *
     * @return $this
     */
    public function assertPlainCookie($cookieName, $value = null)
    {
        $this->assertCookie($cookieName, $value, false);

        return $this;
    }

    /**
     * Assert that the given string is contained within the response.
     *
     * @param string $value
     *
     * @return $this
     */
    public function assertContains($value)
    {
        PHPUnit::assertContains($value, $this->body());

        return $this;
    }

    /**
     * Assert that the given string is contained within the response text.
     *
     * @param string $value
     *
     * @return $this
     */
    public function assertSeeText($value)
    {
        PHPUnit::assertContains($value, strip_tags($this->body()));

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response.
     *
     * @param string $value
     *
     * @return $this
     */
    public function assertDontSee($value)
    {
        PHPUnit::assertNotContains($value, $this->body());

        return $this;
    }

    /**
     * Assert that the given string is not contained within the response text.
     *
     * @param string $value
     *
     * @return $this
     */
    public function assertDontSeeText($value)
    {
        PHPUnit::assertNotContains($value, strip_tags($this->body()));

        return $this;
    }

    /**
     * Assert that the response is a superset of the given JSON.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertJson(array $data)
    {
        PHPUnit::assertArraySubset(
            $data,
            $this->jsonDecoded(),
            false,
            $this->assertJsonMessage($data)
        );

        return $this;
    }

    /**
     * Get the assertion message for assertJson.
     *
     * @param array $data
     *
     * @return string
     */
    protected function assertJsonMessage(array $data)
    {
        $expected = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $actual = json_encode(
            $this->jsonDecoded(),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        return Util::makePhpUnitExpectationMessage(
            'Unable to find json',
            $expected,
            $actual
        );
    }

    /**
     * Assert that the response has the exact given JSON.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertExactJson(array $data)
    {
        $actual = json_encode(Util::recursiveArraySort(
            (array) $this->jsonDecoded()
        ));

        PHPUnit::assertEquals(json_encode(Util::recursiveArraySort($data)), $actual);

        return $this;
    }

    /**
     * Assert that the response contains the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonFragment(array $data)
    {
        $actual = json_encode(Util::recursiveArraySort(
            (array) $this->jsonDecoded()
        ));

        foreach (Util::recursiveArraySort($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            $error = Util::makePhpUnitExpectationMessage(
                'Unable to find json fragment',
                $expected,
                $actual
            );

            PHPUnit::assertTrue(strpos($actual, $expected) !== false, $error);
        }

        return $this;
    }

    /**
     * Assert that the response does not contain the given JSON fragment.
     *
     * @param array $data
     *
     * @return $this
     */
    public function assertJsonMissing(array $data)
    {
        $actual = json_encode(Util::recursiveArraySort(
            (array) $this->jsonDecoded()
        ));

        foreach (Util::recursiveArraySort($data) as $key => $value) {
            $expected = substr(json_encode([$key => $value]), 1, -1);

            $error = Util::makePhpUnitExpectationMessage(
                'Found unexpected json fragment',
                $expected,
                $actual
            );

            PHPUnit::assertFalse(strpos($actual, $expected) !== false, $error);
        }

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     *
     * @param array|null $structure
     * @param array|null $responseData
     *
     * @return $this
     */
    public function assertJsonStructure(array $structure = null, $data = null)
    {
        if (is_null($structure)) {
            return $this->assertJson($this->jsonDecoded());
        }

        if (is_null($data)) {
            $data = $this->jsonDecoded();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && $key === '*') {
                PHPUnit::assertInternalType('array', $data);

                foreach ($data as $entry) {
                    $this->assertJsonStructure($value, $entry);
                }

                continue;
            }

            if (is_array($value)) {
                PHPUnit::assertArrayHasKey($key, $data);

                $this->assertJsonStructure($structure[$key], $data[$key]);

                continue;
            }

            PHPUnit::assertArrayHasKey($value, $data);
        }

        return $this;
    }
}
