<?php

use Moccalotto\Hayttp\Request as Hayttp;

require 'vendor/autoload.php';

//--------------------------------
// Send some json
//--------------------------------
// In this scenario, we send  some json,
// this puts the request into "raw" mode, and it also
// locks the content. We cannot overwrite the content now
// print Hayttp::post('https://eu.httpbin.org/post')
//     ->sendsJson(['this' => 'object', 'will' => 'be', 'converted' => 'to json', 'foreign' => 'lommel'])
//     ->send();

// Send raw blob
// going into raw mode always locks the contents
// print Hayttp::post('https://eu.httpbin.org/post')
//     ->sendsRaw("csv;data\nkey1;value1\nkey2;value2", 'text/csv')
//     ->send();

// Send traditional post data
//print Hayttp::post('https://eu.httpbin.org/post')
//    ->sends(['Friends' => ['Lisa', 'Andy']])
//    ->send();

//---------------------------------------------
// Send files and other data to the server
//---------------------------------------------
// In this scenario we add files to the request.
// This puts the request into "multipart" mode,
// but it does not lock the contents for further
// updates.
// print Hayttp::post('http://eu.httpbin.org/post')
print Hayttp::post('http://localhost:8000')
    ->withEngine(new Moccalotto\Hayttp\Engines\CurlEngine())
    ->addMultipartField('file1', 'GIF87a.............,...........D..;', 'r.gif', 'image/gif')
    ->addFile('file2', 'test.html')
    ->send()
    ;
