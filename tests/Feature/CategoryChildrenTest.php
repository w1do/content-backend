<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('category index returns children when they exist', function () {
    $parent = Category::factory()->create(['name' => 'Parent', 'slug' => 'parent']);
    $child = Category::factory()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);

    $response = $this->getJson('/api/v1/categories');

    $response->assertStatus(200);

    // Check if Parent has Child in children array
    $data = $response->json('data');
    $parentInResponse = collect($data)->firstWhere('id', $parent->id);

    expect($parentInResponse['children'])->not->toBeEmpty();
    expect($parentInResponse['children'][0]['id'])->toBe($child->id);
});

test('category show returns children when they exist', function () {
    $parent = Category::factory()->create(['name' => 'Parent', 'slug' => 'parent']);
    $child = Category::factory()->create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);

    $response = $this->getJson("/api/v1/categories/{$parent->id}");

    $response->assertStatus(200);

    $response->assertJsonPath('data.children.0.id', $child->id);
});
