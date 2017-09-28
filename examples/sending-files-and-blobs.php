<?php

use Hayttp\Hayttp;
use Hayttp\Request;
use Hayttp\Response;

require '_init.php';

/*
---------------------------------------------------------------------------
| SENDING FILES AND LOBS
|--------------------------------------------------------------------------
| In the examples below we illustrate how to send files and large objects
| encoded as multipart/form-data.
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| POSTing a file
|--------------------------------------------------------------------------
| In this example we make a POST request to an example URL, attaching a
| field called 'file', containing a single file called foo.xml
*/
$response = Hayttp::post('https://foo.dev/post')
    ->addFile(
        //
        // ----------------------------------------------------------------
        // The name of the POST field.
        // This field is required.
        'file',
        //
        // ----------------------------------------------------------------
        // The complete name of the file on the server.
        // this field is required and must point
        // to an file that exists on your server
        // and is readable by the current process.
        '/path/to/foo.xml',
        //
        // ----------------------------------------------------------------
        // the name of the attached file.
        // It indicates what the file is called on your server
        // This field is optional.
        // If it is ommitted, no filename hint is sent.
        'foo.xml',
        //
        // ----------------------------------------------------------------
        // The value of Content-Type header.
        // This field is optional.
        // If it is ommitted, Hayttp will try and
        // infer the content type, and it will
        // fall back to application/octet-stream.
        'application/xml'
    )->send();


/*
|--------------------------------------------------------------------------
| POSTing a blob
|--------------------------------------------------------------------------
| In this example we make a POST request to an example URL, attaching a
| field called 'blob', containing a single "file".
| Note that when posting BLOBs, no filename will be sent to the
| remote server.
*/
$response = Hayttp::post('https://foo.dev/post')
    ->addBlob(
        //
        // ----------------------------------------------------------------
        // The name of the POST field, just line the addFile() example.
        'blob',
        //
        // ----------------------------------------------------------------
        // The data to post.
        // This field is required.
        // In this example, we post a tiny GIF image.
        base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==', true),
        //
        // ----------------------------------------------------------------
        // The value of the Content-Type header.
        // This field is optional.
        // If ommitted, no Content-Type header will be sent.
        // When not sending a content-type, the rmeote server should
        // parse the field as if it were a normal form field.
        'iamge/gif'
    )->send();


/*
|--------------------------------------------------------------------------
| Advanced BLOB posting
|--------------------------------------------------------------------------
| In this example we make a POST request to an example URL, attaching a
| field called 'advanced_blob', containing a single file called foo.gif
*/
$response = Hayttp::post('https://foo.dev/post')
    ->addMultipartField(
        //
        // ----------------------------------------------------------------
        // The name of the POST field, just line the addFile() example.
        'advanced_blob',
        //
        // ----------------------------------------------------------------
        // The data to post, just like the addBlob() example.
        base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==', true),
        //
        // ----------------------------------------------------------------
        // The name of the attached "file", just like the addFile() example.
        'r.gif',
        //
        // ----------------------------------------------------------------
        // The Content-Type header, just like the addBlob() example.
        'image/gif'
    );


/*
|--------------------------------------------------------------------------
| POSTing multiple files.
|--------------------------------------------------------------------------
| In this example we make a POST request to an example URL.
| We attach multiple files in multiple fields.
| We add two pdfs with CVs for Bob and Alice.
| We then add two more PDFs with cover letters for the both.
| We then add photos for Bob and Alice, but without giving filename h
| We then add two conventional form fields, containing the names
| of Bob and Alice respectively.
*/
$response = Hayttp::post('https://foo.dev/post')
    ->addFile('cvs[]', 'cvs/cv1.pdf', 'bob.pdf')
    ->addFile('cvs[]', 'cvs/cv2.pdf', 'alice.pdf')
    ->addFile('cover-letters[]', 'cls/cl1.pdf', 'cover-bob.pdf')
    ->addFile('cover-letters[]', 'cls/cl2.pdf', 'cover-alice-2.pdf')
    ->addFile('photos[]', 'photos/face1.png', 'bob.png')
    ->addFile('photos[]', 'photos/face2.png', 'alice.png')
    ->addMultipartField('names[]', 'Bob Dude')
    ->addMultipartField('names[]', 'Alice Dudette')
    ->send();
