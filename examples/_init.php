<?php

use Moccalotto\Hayttp\Hayttp;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Mock\Route;

require __DIR__ . '/../vendor/autoload.php';

// mock all calls so that the examples can be run without side effects
Hayttp::mockEndpoint('.*', '{anything}', function (Request $request, Route $route) {
    $request = Hayttp::createMockResponse($request, $route);

    if ($request->header('accept') === 'application/json') {
        return $request->withJsonBody(['demo' => true]);
    }

    if ($request->header('accept') === 'application/xml') {
        return $request->withXmlBody('<root>foo</root>');
    }

    return $request;
});
