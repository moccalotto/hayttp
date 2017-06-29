<?php

$mock = Hayttp::mockCall('post', 'https://diller.dollar.com/42.json');
$mock->request('post', 'https://domain.tld/path')
    ->assertContentType('application/json')
    ->assertContentFragment(['anus' => 'penis'])
    ->assertContentType(['plankebøf'])
    ->respondsWith(function ($response) {
        $response->statusCode(200)
            ->jsonBody([
                'lem' => 'slem'
            ])
            ->headers([
                'X-Foo' => 'bar'
            ]);
    });


Hayttp::mockServer('post', 'https://myapi.dev/api/v2/cats', function ($request, $response) {
    $request->assertJson();
    $request->assertContains('cat');

    $response = $request->send();

    $response->assert2xx();

    return $response;
});

Hayttp::mockServer('get', 'https://myapi.dev/api/v2/cats/{id}', function ($request, $response, $route) {

    $route->assertHas('id');
    $route->assertInteger('id');

    $response = $request->send();

    $response->assert2xx();
    $response->assertJsonBody([
        'cat' => 'required && isObject',
        'cat.family' => 'required && isString',
        'cat.legs' => 'required && isInteger',
    ]);

    return $response;
});
