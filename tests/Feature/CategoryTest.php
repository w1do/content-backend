<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('можно получить дерево категорий и товары по иерархии', function () {
    // Создаем иерархию: оборудование -> газовое оборудование -> баллоны
    $root = Category::factory()->create(['name' => 'Оборудование', 'slug' => 'oborudovanie']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'slug' => 'gazovoe', 'parent_id' => $root->id]);
    $grandChild = Category::factory()->create(['name' => 'Баллоны', 'slug' => 'ballony', 'parent_id' => $child->id]);

    // Создаем продукты
    $p1 = Product::factory()->create(['name' => 'Баллон 50л']);
    $p2 = Product::factory()->create(['name' => 'Котел газовый']);

    $p1->categories()->attach($grandChild->id);
    $p2->categories()->attach($child->id);

    // Проверяем дерево
    $response = $this->getJson('/api/v1/categories/tree');
    $response->assertStatus(200);
    // В упрощенном контроллере дерево возвращается как список с учетом порядка вложенности (Nested Set default order)

    // Проверяем продукты в корневой категории (должны быть все из-за include_children=true по умолчанию)
    $response = $this->getJson("/api/v1/categories/{$root->id}/products");
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data');

    // Проверяем продукты в самой глубокой категории
    $response = $this->getJson("/api/v1/categories/{$grandChild->id}/products");
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Баллон 50л');

    // Проверяем хлебные крошки для баллонов
    $response = $this->getJson("/api/v1/categories/{$grandChild->id}/breadcrumbs");
    $response->assertStatus(200)
        ->assertJsonCount(2, 'data') // Предки: Оборудование, Газовое оборудование
        ->assertJsonPath('data.0.name', 'Оборудование')
        ->assertJsonPath('data.1.name', 'Газовое оборудование');
});

test('full_path новой корневой категории равен ее slug', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);

    expect($root->full_path)->toBe($root->slug);
});

test('full_path вложенной категории включает путь родителя', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'parent_id' => $root->id]);
    $grandChild = Category::factory()->create(['name' => 'Баллоны', 'parent_id' => $child->id]);

    expect($child->full_path)->toBe("{$root->slug}/{$child->slug}")
        ->and($grandChild->full_path)->toBe("{$root->slug}/{$child->slug}/{$grandChild->slug}");
});

test('при перемещении категории full_path обновляется у нее и у всех потомков', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);
    $newRoot = Category::factory()->create(['name' => 'Запчасти']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'parent_id' => $root->id]);
    $grandChild = Category::factory()->create(['name' => 'Баллоны', 'parent_id' => $child->id]);

    $child->parent_id = $newRoot->id;
    $child->save();
    $child->refresh();
    $grandChild->refresh();

    expect($child->full_path)->toBe("{$newRoot->slug}/{$child->slug}")
        ->and($grandChild->full_path)->toBe("{$newRoot->slug}/{$child->slug}/{$grandChild->slug}");
});

test('при переименовании категории full_path обновляется у нее и у потомков', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'parent_id' => $root->id]);
    $originalSlug = $root->slug;

    $root->name = 'Новое оборудование';
    $root->save();
    $root->refresh();
    $child->refresh();

    expect($root->slug)->not->toBe($originalSlug)
        ->and($root->full_path)->toBe($root->slug)
        ->and($child->full_path)->toBe("{$root->slug}/{$child->slug}");
});

test('дерево категорий содержит full_path и вложенные children', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'parent_id' => $root->id]);

    $response = $this->getJson('/api/v1/categories/tree');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.full_path', $root->slug)
        ->assertJsonPath('data.0.children.0.full_path', "{$root->slug}/{$child->slug}");
});

test('ProductResource содержит полный путь категории товара', function () {
    $root = Category::factory()->create(['name' => 'Оборудование']);
    $child = Category::factory()->create(['name' => 'Газовое оборудование', 'parent_id' => $root->id]);

    $product = Product::factory()->create(['name' => 'Котел газовый']);
    $product->categories()->attach($child->id);

    $response = $this->getJson("/api/v1/categories/{$child->id}/products");

    $response->assertStatus(200)
        ->assertJsonPath('data.0.category_full_path', "{$root->slug}/{$child->slug}");
});
