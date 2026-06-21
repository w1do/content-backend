<?php

test('products index returns json resource collection', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [],
        ]);
});

test('product show returns json resource', function () {
    $response = $this->getJson('/api/v1/products/1');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'category_id',
                'name',
                'slug',
                'description',
                'attributes',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => '1',
                'name' => 'Dummy',
            ],
        ]);
});

test('categories index returns json resource collection', function () {
    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [],
        ]);
});

test('category show returns json resource', function () {
    $response = $this->getJson('/api/v1/categories/1');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'id',
                'parent_id',
                'name',
                'slug',
                'status',
                'description',
            ],
        ])
        ->assertJson([
            'data' => [
                'id' => '1',
                'name' => 'Dummy Category',
            ],
        ]);
});
