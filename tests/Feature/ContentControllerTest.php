<?php

use App\Domain\Enums\ContentType;
use App\Infrastructure\Persistence\Eloquent\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Content::factory()->create([
        'type' => ContentType::Blog,
        'name' => 'Blog Post',
        'slug' => 'blog-post',
    ]);

    Content::factory()->create([
        'type' => ContentType::Page,
        'name' => 'About Page',
        'slug' => 'about-page',
    ]);

    Content::factory()->create([
        'type' => ContentType::System,
        'name' => 'Privacy Policy',
        'slug' => 'privacy',
    ]);

    Content::factory()->create([
        'type' => ContentType::Material,
        'name' => 'Material Item',
        'slug' => 'material-item',
    ]);
});

it('can get blog posts', function () {
    $response = $this->getJson('/api/v1/blog');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'blog')
        ->assertJsonPath('data.0.name', 'Blog Post');
});

it('can get pages', function () {
    $response = $this->getJson('/api/v1/page');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'page')
        ->assertJsonPath('data.0.name', 'About Page');
});

it('can get system pages', function () {
    $response = $this->getJson('/api/v1/system');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'system')
        ->assertJsonPath('data.0.name', 'Privacy Policy');
});

it('can get materials via content endpoint', function () {
    $response = $this->getJson('/api/v1/content');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'material')
        ->assertJsonPath('data.0.name', 'Material Item');
});
