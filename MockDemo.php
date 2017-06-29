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


Hayttp::mockServer('post', 'https://myapi.dev/api/v2/cats', function ($request) {
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

Hayttp::mockServer('get', 'https://myapi.dev/api/v2/cats/{id}', function ($request, $route) {

    $route->assertHas('id');
    $route->assertInteger('id');

    // send the request to the API instead of creating a new one
    $response = $request->send();

    $response->assert2xx();
    $response->assertJsonBody([
        'cat' => 'required && isObject',
        'cat.family' => 'required && isString',
        'cat.legs' => 'required && isInteger',
        'cat.name' => 'required && isShorterThan(255)',
    ]);

    return $response;
});

// Ideas for implementing a router:

$routeRegex = preg_replace('/{([a-z0-9_-]+?)}/', '(?P<$1>.+?)', $routeDefinition);

if (preg_match($routeRegex, $requst->url(), $matches)) {
     $this->handle($request, new ResponseFactory(), new RouteContainer($matches));
}
