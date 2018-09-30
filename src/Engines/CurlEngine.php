<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2018
 * @license MIT
 */

namespace Hayttp\Engines;

use Hayttp\Request;
use Hayttp\Response;
use Hayttp\Contracts\Engine;
use UnexpectedValueException;
use Hayttp\Exceptions\CouldNotConnectException;

class CurlEngine implements Engine
{
    /**
     * @param string $cryptoMethod
     *
     * @return int
     */
    protected function curlCryptoMethod($cryptoMethod)
    {
        switch ($cryptoMethod) {
            case Request::CRYPTO_ANY:
                return CURL_SSLVERSION_DEFAULT;
            case Request::CRYPTO_SSLV3:
                return CURL_SSLVERSION_SSLv3;
            case Request::CRYPTO_TLS:
                return CURL_SSLVERSION_TLSv1;
            case Request::CRYPTO_TLS_1_0:
                return defined(CURL_SSLVERSION_TLSv1_0)
                    ? CURL_SSLVERSION_TLSv1_0
                    : CURL_SSLVERSION_TLSv1;
            case Request::CRYPTO_TLS_1_1:
                return defined(CURL_SSLVERSION_TLSv1_1)
                    ? CURL_SSLVERSION_TLSv1_1
                    : CURL_SSLVERSION_TLSv1;
            case Request::CRYPTO_TLS_1_2:
                return defined(CURL_SSLVERSION_TLSv1_2)
                    ? CURL_SSLVERSION_TLSv1_2
                    : CURL_SSLVERSION_TLSv1;
        }

        throw new UnexpectedValueException('Unknown cryptoMethod');
    }

    protected function buildHandle(Request $request)
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
        curl_setopt($ch, CURLOPT_SSLVERSION, $this->curlCryptoMethod($request->cryptoMethod()));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $body = $request->body();
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

    /**
     * Send/execute the request.
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws CouldNotConnectException if connection could not be established
     */
    public function send(Request $request)
    {
        $ch = $this->buildHandle($request);

        $result = curl_exec($ch);

        if ($result === false) {
            throw new CouldNotConnectException($request, [
                'curl_error' => curl_error($ch),
                'curl_errno' => curl_errno($ch),
                'curl_info' => curl_getinfo($ch),
            ]);
        }

        list($headersText, $body) = explode("\r\n\r\n", $result, 2);

        $headers = explode("\r\n", $headersText);
        $metadata = curl_getinfo($ch);

        return new Response($body, $headers, $metadata, $request);
    }
}
