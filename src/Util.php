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
    public static function applyMountPoint(string $pathOrUrl, $mountPoint)
    {
        if (!$mountPoint) {
            return $pathOrUrl;
        }

        if (parse_url($pathOrUrl, PHP_URL_SCHEME)) {
            return $pathOrUrl;
        }

        static::ensureValidUrl(vsprintf('%s/%s', [
            rtrim($this->mountPoint, '/'),
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
}
