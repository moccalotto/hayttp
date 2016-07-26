<?php

use Moccalotto\Hayttp\Request;

require 'vendor/autoload.php';

$response = Request::get('https://eu.httpbin.org/status/400')
    ->withTls(1.2)
    ->withLogging()
    ->send();
/*
//--------------------------------
// Send some json
//--------------------------------
// In this scenario, we send  some json,
// this puts the request into "raw" mode, and it also
// locks the content. We cannot overwrite the content now

Hayttp::post('https://httpbin.org')
    ->sendsJson(['this' => 'object', 'will' => 'be', 'converted' => 'to json'])
    ->send();


// Send raw blob
// going into raw mode always locks the contents
Hayttp::post('https://httpbin.org')
    ->sendsRaw("csv;data\nkey1;value1\nkey2;value2", 'text/csv')
    ->send();

// Send traditional post data
Hayttp::post('https://httpbin.org')->addPostField('name', 'Carlo')
    ->addPostField('Friends[]', ['Lisa', 'Andy'])
    ->send();

//---------------------------------------------
// Send files and other data to the server
//---------------------------------------------
// In this scenario we add files to the request.
// This puts the request into "multipart" mode,
// but it does not lock the contents for further
// updates.
Hayttp::post('https://httpbin.org')
    ->addFile('sourceDocument', 'something.rtf')
    ->addFile('translations', ['de.rtf', 'english.rtf'])
    ->addMultipartField('receipt', json_encode($receipt), 'application/json')
    ->send();
 */
