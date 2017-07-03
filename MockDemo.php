<?php

Hayttp::mockEndpoint('post', 'https://myapi.dev/api/v2/cats', function ($request) {
    $response = hayttp()->createMockResponse($request)
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

    // send the request to the API instead of creating a new one
    $response = $request->passthru();

    return $response;
});
