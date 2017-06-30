<?php

Hayttp::mockEndpoint('post', 'https://myapi.dev/api/v2/cats', function ($request) {
    $request->assertJson();
    $request->assertContains('cat');

    $response = $request->createMockResponse()
        ->withHeaders([
            'Content-Type' => 'application/json',
        ])->withJsonBody([
            'cats' => [
                'cat' => [
                    'family' => 'Tiger',
                    'legs' => 4,
                    'name' => 'Hugo',
                ],
            ]
        ]);

    return $response;
});

Hayttp::mockEndpoint('get', 'https://myapi.dev/api/v2/cats/{id}', function ($request, $route) {

    $route->assertHas('id');
    $route->assertInteger('id');

    // send the request to the API instead of creating a new one
    $response = $request->passthru();

    $response->assert2xx();
    $response->assertJsonBody([
        'cat' => 'required && isObject',
        'cat.family' => 'required && isString',
        'cat.legs' => 'required && isInteger',
        'cat.name' => 'required && isShorterThan(255)',
    ]);

    return $response;
});
