<?php

use Moccalotto\Hayttp\Hayttp;
use Moccalotto\Hayttp\Request;
use Moccalotto\Hayttp\Response;

require '../vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| MAKING REQUESTS
|--------------------------------------------------------------------------
| In the examples below we make a number of requests, each returning
| objects of the class Moccalotto\Hayttp\Response.
| 
| There are static helpers for the following methods:
| - get
| - post
| - put
| - patch
| - delete
| - head
| - options
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Simple GET Request via the Hayttp facade.
|--------------------------------------------------------------------------
| In this example we make a GET request to an example URL and extract
| the raw string body from the response.
*/
$responseObj = Hayttp::get('http://foo.dev/foo')->send();
$rawBodyStr  = $responseObj->body();
$statusCode  = $responseObj->statusCode();


/*
|--------------------------------------------------------------------------
| PUTing JSON via the Hayttp facade
|--------------------------------------------------------------------------
| In this example we make a PUT request to an example URL and
| deserialize the response body into a native PHP data structure.
*/
$responseObj = Hayttp::put('https://example.org')
    ->sendJson([
        'this' => 'array',
        'will' => 'be',
        'converted' => 'to',
        'a' => 'json object'
    ]);

// The body of the response is json decoded into php arrays
// and StdClass objects.
$jsonBody = $responseObj->jsonDecoded();


/*
|--------------------------------------------------------------------------
| POSTing url-encoded form data via the Hayttp facade.
|--------------------------------------------------------------------------
| In this example we make a POST request to an example URL and
| deserialize the response into a SimpleXmlElement object.
| 
*/
$responseObj = Hayttp::post('https://example.org')
    ->sendFormData([
        'this' => 'array',
        'will' => 'be',
        'url' => 'encoded',
        'as' => 'application/x-www-form-urlencoded'
    ]);

$xmlBody = $responseObj->xmlDecoded();

/*
|--------------------------------------------------------------------------
| DELETEing and decoding
|--------------------------------------------------------------------------
| In this example we make a DELETE request to an example URL and
| automatically infer the response data type by inspecting the 'Content-Type'
| response header.
| 
*/
$responseObj = Hayttp::delete('https://example.org')->send();

// the resposne body may be a string, PHP array, StdClass object
// or SimpleXmlElement object, depending on the Content-Type
// header of the response.
$responseBody = $responseObj->decoded();
