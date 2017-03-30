<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Traits;

use UnexpectedValueException;
use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Payload as PayloadContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;

trait HasWithMethods
{
    /**
     * Clone object with a new property value.
     *
     * @param string $property
     * @param mixed  $value
     *
     * @return RequestContract
     */
    abstract protected function with($property, $value) : RequestContract;

    /**
     * Set the timeout.
     *
     * @param float $timeout
     *
     * @return RequestContract
     */
    public function withTimeout(float $timeout) : RequestContract
    {
        return $this->with('timeout', $timeout);
    }

    /**
     * Set the allowed crypto method.
     *
     * A Crypto method can be one of the CRYPTO_* constants
     *
     * @param string
     *
     * @return RequestContract
     */
    public function withCryptoMethod($cryptoMethod) : RequestContract
    {
        if (!isset(RequestContract::CRYPTO_METHODS[$cryptoMethod])) {
            throw new UnexpectedValueException(sprintf(
                'Crypto methed "%s" is invalid. Must be one of [%s]',
                $cryptoMethod,
                implode(', ', array_keys(RequestContract::CRYPTO_METHODS))
            ));
        }

        return $this->with('cryptoMethod', $cryptoMethod);
    }

    /**
     * Disable all SSL certificate checks.
     *
     * @return RequestContract
     */
    public function withInsecureSsl() : RequestContract
    {
        return $this->with('secureSsl', false);
    }

    /**
     * Set the transfer engine.
     *
     * @param EngineContract $engine
     *
     * @return RequestContract
     */
    public function withEngine(EngineContract $engine) : RequestContract
    {
        return $this->with('engine', $engine);
    }

    /**
     * Set all headers.
     *
     * @param array $headers
     *
     * @return RequestContract
     */
    public function withHeaders(array $headers) : RequestContract
    {
        return $this->with('headers', $headers);
    }

    /**
     * Set the proxy server.
     *
     * @param string $proxy URI specifying address of proxy server. (e.g. tcp://proxy.example.com:5100).
     *
     * @return RequestContract
     */
    public function withProxy($proxy) : RequestContract
    {
        return $this->with('proxy', $proxy);
    }

    /**
     * Add a header to the request.
     *
     * @param string $name
     * @param string $value
     *
     * @return RequestContract
     */
    public function withHeader($name, $value) : RequestContract
    {
        return $this->withHeaders(array_merge($this->headers, [$name => $value]));
    }

    /**
     * Set the TLS version.
     *
     * @param string $version currently, 1.*, 1.0, 1.1 and 1.2 are supported
     *
     * @return RequestContract
     */
    public function withTls($version)
    {
        switch ($version) {
        case '1.*':
            return $this->withCryptoMethod(static::CRYPTO_TLS);
        case '1.0':
            return $this->withCryptoMethod(static::CRYPTO_TLS_1_0);
        case '1.1':
            return $this->withCryptoMethod(static::CRYPTO_TLS_1_1);
        case '1.2':
            return $this->withCryptoMethod(static::CRYPTO_TLS_1_2);
        default:
            throw new UnexpectedValueException(sprintf(
                'TLS version "%s" is unavailable. Must be one of: [1.*, 1.0, 1.1, 1.2]',
                $version
            ));
        }
    }

    /**
     * Add a basic authorization (which is actually an authenticaation) header.
     *
     * @param string $username
     * @param string $password
     *
     * @return RequestContract
     */
    public function withBasicAuth(string $username, string $password) : RequestContract
    {
        return $this->withHeader(sprintf(
            'Authorization: Basic %s',
            base64_encode(sprintf('%s:%s', $username, $password))
        ));
    }

    public function withPayload(PayloadContract $payload)
    {
        return $this->with('payload', $payload);
    }
}
