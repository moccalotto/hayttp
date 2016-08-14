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

class NativeEngine implements EngineContract
{
    /**
     * @var array
     */
    protected $cryptoMap = [
        RequestContract::CRYPTO_ANY => STREAM_CRYPTO_METHOD_ANY_CLIENT,
        RequestContract::CRYPTO_SSLV3 => STREAM_CRYPTO_METHOD_SSLv3_CLIENT,
        RequestContract::CRYPTO_TLS => STREAM_CRYPTO_METHOD_TLS_CLIENT,
        RequestContract::CRYPTO_TLS_1_0 => STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT,
        RequestContract::CRYPTO_TLS_1_1 => STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT,
        RequestContract::CRYPTO_TLS_1_2 => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT,
    ];

    protected function buildContext(RequestContract $request)
    {
        $cryptoMethodFlag = $this->cryptoMap[$request->cryptoMethod];

        $options = [
            'http' => [ // http://php.net/manual/en/context.http.php
                'method' => $request->method,
                'user_agent' => $request->userAgent,
                'proxy' => $request->proxy,
                'follow_location' => false,
                'max_redirects' => 0,
                'timeout' => $request->timeout,
                'protocol_version' => 1.0,
                'ignore_errors' => true,
                'header' => $request->preparedHeaders(),
                'content' => (string) $request->body,
            ],
            'ssl' => [ // http://php.net/manual/en/context.ssl.php
                'verify_peer' => $request->secureSsl,
                'verify_peer_name' => $request->secureSsl,
                'allow_self_signed' => ! $request->secureSsl,
                'verify_depth' => 10,
                'crypto_method' => $cryptoMethodFlag,
                // disable compression to prevent CRIME attack.
                // only necessary if an external user can affect
                // the message (cookie, etc.)
                'disable_compression' => true,

            ],
        ];

        return stream_context_create($options, []);
    }

    /**
     * Send/execute the request.
     *
     * @return ResponseContract
     *
     * @throws ConnectionException if connection could not be established.
     */
    public function send(RequestContract $request) : ResponseContract
    {
        $responseBody = file_get_contents($request->url, false, $this->buildContext($request));

        if ($responseBody === false) {
            $success = false;
            $responseHeaders = [];
        } else {
            $success = true;
            $responseHeaders = $http_response_header;
        }

        return new Response($responseBody, $responseHeaders, $request);
    }
}
