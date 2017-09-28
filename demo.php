<?php

/**
 * This file is part of the Hayttp package.
 *
 * @author Kim Ravn Hansen <moccalotto@gmail.com>
 * @copyright 2017
 * @license MIT
 */
use Hayttp\Hayttp;
use Hayttp\Request;
use Hayttp\Response;

require 'vendor/autoload.php';

// Dynamically add a method to all requests
Request::extend('printme', function () {
    return print_r($this, true);
});

Response::extend('printme', function () {
    return print_r($this, true);
});

// Create a "controller" for the given end point
Hayttp::mockEndpoint('get', '{scheme}://foo.dev/{path}', function ($request, $route) {
    // make a call to the new and fancy end extended method
    // print $request->printme();

    return Hayttp::createMockResponse($request, $route)
        ->withJsonBody(['demo' => true])
        ->withRoute($route);
});

$response = Hayttp::get('http://foo.dev/foo')->send()
    ->ensureStatus('200')
    ->printme();

die();

//--------------------------------
// Send some json
//--------------------------------
// In this scenario, we send  some json,
// this puts the request into "raw" mode, and it also
// locks the content. We cannot overwrite the content now
$response = Hayttp::post('https://example.org')
     ->sendJson(['this' => 'object', 'will' => 'be', 'converted' => 'to json', 'foreign' => 'lommel']);

// Send raw blob
// going into raw mode always locks the contents
$response = Hayttp::post('https://example.org/post')
     ->sendRaw("csv;data\nkey1;value1\nkey2;value2", 'text/csv');

// Send traditional post data
$response = Hayttp::post('https://example.org/post')
    ->sendFormData(['Friends' => ['Lisa', 'Danni']]);

//---------------------------------------------
// Send files and other data to the server
//---------------------------------------------
// In this scenario we add files to the request.
// This puts the request into "multipart" mode,
// but it does not lock the contents for further
// updates.
$response = Hayttp::post('https://example.org/post')
    ->addMultipartField(
        'file1',
        base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==', true),
        'r.gif',
        'image/gif'
    )->addMultipartField('file2', '<html><body>Naked</body></html>', 't.html', 'text/html')
    ->send();

//---------------------------------------------
// Transforming responses
//---------------------------------------------
// You can use the transform method on responses
// to convert the response in a structured way.
$jsonArray = function ($response) {
    return json_decode($response->body, true);
};

$friends = Hayttp::get('https://example.com/friends')
    ->expectJson()
    ->send()
    ->transform($jsonArray)['friends'];

$cats = Hayttp::get('https://example.com/cats')
    ->expectJson()
    ->send()
    ->transform($jsonArray)['cats'];

//---------------------------------------------
// Mixing it up
//---------------------------------------------
// In this scenario we change the http transfer engine.
// we enforce a given ssl/tls version
// we set the timeout to 10 seconds.
// we make Hayttp throw a ResponseException if the
// content type is not json and if the http status code is not 200
// once we have the response, we transform it.
$friends = Hayttp::get('https://example.com/friends')
    ->withEngine(new Hayttp\Engines\CurlEngine())
    ->withCryptoMethod('tlsv1.2')   // send data via TLS version 1.2.
    ->withTimeout(10.0)             // set a 10-second timeout.
    ->ensure200()                   // Ensure that the response code is 200.
    ->ensureJson()                  // Throw exception if valid json data is not returned.
    ->send()
    ->transform(function ($responseObj) {
        // transform the response
        return $responseObj->decoded()->friends;
    });
