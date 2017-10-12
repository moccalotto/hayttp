<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */

namespace Hayttp\Engines;

use Hayttp\Contracts\Engine as EngineContract;
use Hayttp\Contracts\Request as RequestContract;
use Hayttp\Contracts\Response as ResponseContract;
use Hayttp\Exceptions\CouldNotConnectException;
use Hayttp\Response as Response;
use ErrorException;

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
        $cryptoMethodFlag = $this->cryptoMap[$request->cryptoMethod()];

        $options = [
            'http' => [ // http://php.net/manual/en/context.http.php
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'proxy' => $request->proxy(),
                'follow_location' => false,
                'max_redirects' => 0,
                'timeout' => $request->timeout(),
                'protocol_version' => 1.0,
                'ignore_errors' => true,
                'header' => $request->preparedHeaders(),
                'content' => (string) $request->body(),
            ],
            'ssl' => [ // http://php.net/manual/en/context.ssl.php
                'verify_peer' => $request->secureSsl(),
                'verify_peer_name' => $request->secureSsl(),
                'allow_self_signed' => !$request->secureSsl(),
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
     * @throws CouldNotConnectException if connection could not be established
     */
    public function send(RequestContract $request) : ResponseContract
    {
        try {
            set_error_handler(function ($errorNumber, $errorMessage, $file, $line) use ($request) {
                throw new ErrorException(
                    $errorMessage,
                    $errorNumber,
                    E_ERROR,
                    $file,
                    $line
                );
            });

            $stream = fopen($request->url(), 'r', false, $this->buildContext($request));

            restore_error_handler();

            if (!$stream) {
                throw new CouldNotConnectException($request);
            }
        } catch (ErrorException $e) {
            // Reached if fancy php error-exception handler is running
            // and fopen fails
            throw new CouldNotConnectException($request, [], $e);
        }

        $body = stream_get_contents($stream);
        $metadata = stream_get_meta_data($stream);
        $headers = $metadata['wrapper_data'];

        return new Response($body, $headers, $metadata, $request);
    }
}
