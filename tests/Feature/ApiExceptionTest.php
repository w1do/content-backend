<?php

test('api handles domain exception and returns json', function () {
    $response = $this->getJson('/api/v1/products/fail-domain');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Entity Product with ID fail-domain not found.',
            'type' => 'domain_error',
        ]);
});

test('api handles 404 for non-existent routes as json', function () {
    $response = $this->getJson('/api/v1/non-existent');

    $response->assertStatus(404);
    // Laravel default 404 for JSON
});
