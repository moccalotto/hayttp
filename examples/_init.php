<?php

use Moccalotto\Hayttp\Hayttp;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Mock\Route;

require __DIR__ . '/../vendor/autoload.php';

// mock all calls so that the examples can be run without side effects
Hayttp::mockEndpoint('.*', '{anything}', function (Request $request, Route $route) {
    $response = Hayttp::createMockResponse($request, $route);

    if ($request->header('accept') === 'application/json') {
        return $response->withJsonBody(['demo' => true]);
    }

    if ($request->header('accept') === 'application/xml') {
        return $response->withXmlBody('<root>foo</root>');
    }

    return $response;
});
