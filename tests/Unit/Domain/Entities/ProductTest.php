<?php

use App\Domain\Entities\Product;

test('it can be instantiated', function () {
    $product = new Product(
        id: 1,
        categoryId: 2,
        name: 'Test Product',
        slug: 'test-product',
        description: 'Test Description',
        attributes: ['key' => 'value']
    );

    expect($product->id)->toBe(1)
        ->and($product->categoryId)->toBe(2)
        ->and($product->name)->toBe('Test Product')
        ->and($product->slug)->toBe('test-product')
        ->and($product->description)->toBe('Test Description')
        ->and($product->attributes)->toBe(['key' => 'value']);
});
