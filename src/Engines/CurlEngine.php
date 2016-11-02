<?php

/**
 * This file is part of the Hayttp package.
 *
 * @package Hayttp
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2016
 * @license MIT
 */

namespace Moccalotto\Hayttp\Engines;

use Moccalotto\Hayttp\Contracts\Engine as EngineContract;
use Moccalotto\Hayttp\Contracts\Request as RequestContract;
use Moccalotto\Hayttp\Contracts\Response as ResponseContract;
use Moccalotto\Hayttp\Response as Response;
use UnexpectedValueException;
use RuntimeException;

class CurlEngine implements EngineContract
{
    protected function curlCryptoMethod($cryptoMethod)
    {
        switch ($cryptoMethod) {
            case RequestContract::CRYPTO_ANY:
                return CURL_SSLVERSION_DEFAULT;
            case RequestContract::CRYPTO_SSLV3:
                return CURL_SSLVERSION_SSLv3;
            case RequestContract::CRYPTO_TLS:
                return CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_0:
                return defined(CURL_SSLVERSION_TLSv1_0)
                    ? CURL_SSLVERSION_TLSv1_0
                    : CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_1:
                return defined(CURL_SSLVERSION_TLSv1_1)
                    ? CURL_SSLVERSION_TLSv1_1
                    : CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_2:
                return defined(CURL_SSLVERSION_TLSv1_2)
                    ? CURL_SSLVERSION_TLSv1_2
                    : CURL_SSLVERSION_TLSv1;
            default:
                throw new UnexpectedValueException('Unknown cryptoMethod');
        }
    }

    protected function curlCryptoMethod($cryptoMethod)
    {
        switch ($cryptoMethod) {
            case RequestContract::CRYPTO_ANY:
                return CURL_SSLVERSION_DEFAULT;
            case RequestContract::CRYPTO_SSLV3:
                return CURL_SSLVERSION_SSLv3;
            case RequestContract::CRYPTO_TLS:
                return CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_0:
                return defined(CURL_SSLVERSION_TLSv1_0)
                    ? CURL_SSLVERSION_TLSv1_0
                    : CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_1:
                return defined(CURL_SSLVERSION_TLSv1_1)
                    ? CURL_SSLVERSION_TLSv1_1
                    : CURL_SSLVERSION_TLSv1;
            case RequestContract::CRYPTO_TLS_1_2:
                return defined(CURL_SSLVERSION_TLSv1_2)
                    ? CURL_SSLVERSION_TLSv1_2
                    : CURL_SSLVERSION_TLSv1;
            default:
                throw new UnexpectedValueException('Unknown cryptoMethod');
        }
    }

    protected function buildHandle(RequestContract $request)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->url());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->method());
        if ($request->method() === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        }
        if (defined(CURLOPT_TIMEOUT_MS)) {
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, (int) ($request->timeout() * 1000));
        } else {
            curl_setopt($ch, CURLOPT_TIMEOUT, (int) $request->timeout());
        }
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $request->secureSsl());
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $request->secureSsl() ? 2 : 0);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, $request->secureSsl());
        curl_setopt($ch, CURLOPT_SSLVERSION, $this->cryptoMethod($request->cryptoMethod()));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $body = (string) $request->body();
        $headers = $request->preparedHeaders();
        $headers[] = 'Expect:';
        $headers[] = sprintf('Content-Length: %d', strlen($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_PROXY, $request->proxy());

        return $ch;
    }

    protected function assertNoError($ch, $result)
    {
        if ($result === false) {
            throw new RuntimeException(sprintf(
                'Unable to connect. %d %s',
                curl_errno($ch),
                curl_error($ch)
            ));
        }
    }

    /**
     * Send/execute the request.
     *
     * @param RequestContract $request
     *
     * @return ResponseContract
     */
    public function send(RequestContract $request) : ResponseContract
    {
        $ch = $this->buildHandle($request);

        $result = curl_exec($ch);

        $this->assertNoError($ch, $result);

        list($headersText, $body) = explode("\r\n\r\n", $result, 2);

        $headers = explode("\r\n", $headersText);
        $metadata = curl_getinfo($ch);

        return new Response($body, $headers, $metadata, $request);
    }
}
