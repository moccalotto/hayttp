<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Moccalotto\Hayttp;

use UnexpectedValueException;

/**
 * Utility class.
 */
class Util
{
    /**
     * Make a url, possibly prefixing a mount point.
     *
     * @param string      $pathOrUrl  The path or url from wich to generate the url.
     *                                If $pathOrUrl contains a scheme (i.e. http or https)
     *                                it will be used in its entirety without considering the
     *                                mount point.
     *                                Otherwise, the mount point (if any) will be prefixed
     *                                to the $pathOrUrl
     * @param string|null $mountPoint the mount point (if any)
     *
     * @return string
     */
    public static function applyMountPoint(string $pathOrUrl, $mountPoint = null)
    {
        if ($mountPoint === null) {
            return $pathOrUrl;
        }

        if (parse_url($pathOrUrl, PHP_URL_SCHEME)) {
            return $pathOrUrl;
        }

        return static::ensureValidUrl(vsprintf('%s/%s', [
            rtrim($mountPoint, '/'),
            ltrim($pathOrUrl, '/'),
        ]));
    }

    /**
     * Ensure that a url is valid.
     *
     * @param string $url
     *
     * @return string
     */
    public static function ensureValidUrl(string $url)
    {
        $schemes = ['http', 'https'];
        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME));

        if (in_array($scheme, $schemes)) {
            return $url;
        }

        throw new UnexpectedValueException(sprintf(
            'Invalid URL: %s',
            $url
        ));
    }

    /**
     * Recursive array sort (sort by keys if possible, otherwise sort by values).
     *
     * @param array $array
     *
     * @return array
     */
    public static function recursiveArraySort($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = static::recursiveArraySort($value);
            }
        }

        if (array_keys($array) === range(0, count($array) - 1)) {
            sort($array);

            return $array;
        }

        ksort($array);

        return $array;
    }

    /**
     * Json stringify some data.
     *
     * @param mixed $data;
     *
     * @return string
     */
    public static function toJson($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Json stringify some data.
     *
     * @param mixed $data;
     *
     * @return string
     */
    public static function toPrettyJson($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Create an assertion/expectation message for assertions.
     *
     * @param string $message
     * @param mixed $expected
     * @param mixed $actual
     *
     * @return string
     */
    public static function makeExpectationMessage($message, $expected, $actual)
    {
        $expected = static::toPrettyJson($expected);
        $actual   = static::toPrettyJson($actual);
        return $message
            . PHP_EOL
            . PHP_EOL
            . $expected
            . PHP_EOL
            . PHP_EOL
            . 'against'
            . PHP_EOL
            . PHP_EOL
            . $actual
            . PHP_EOL;
    }

    /**
     * Normalize an array of headers.
     *
     * Turns this:
     *
     * [
     *      'HTTP/1.0 200 OK',
     *      'Content-Type ' => ' application/json',
     *      'x-foo-bar: thing',
     *      'x-baz-bing: ',
     * ]
     *
     * into this:
     * [
     *      'HTTP/1.0 200 OK',
     *      'content-type' => 'application/json',
     *      'x-foo-bar' => 'thing',
     *      'x-foo-bing' => '',
     * ]
     *
     * @param array $headers
     *
     * @return array
     */
    public static function normalizeHeaders($headers)
    {
        $res = [];

        foreach ($headers as $key => $value) {
            if (is_int($key) && strpos($value, ':') === false) {
                $res[] = trim($value);
                continue;
            }

            if (is_int($key)) {
                list($key, $value) = explode(':', $value, 2);
            }

            $key = trim(strtolower($key));
            $value = trim($value);

            if (isset($res[$key])) {
                $res[$key] .= ';' . $value;
            } else {
                $res[$key] = $value;
            }
        }

        return $res;
    }

    /**
     * Normalize a header name.
     *
     * content-type becomes Content-Type
     * X-FOO-Bar becomes X-Foo-Bar
     *
     * @param string $headerName
     *
     * @return string
     */
    public static function normalizeHeaderName($headerName)
    {
        return implode(
            '-',
            array_map(
                'ucfirst',
                explode(
                    '-',
                    trim($headerName)
                )
            )
        );
    }
}
