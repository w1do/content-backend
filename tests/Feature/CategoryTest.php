<?php

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('можно создать категорию', function () {
    $data = [
        'name' => 'Оборудование',
        'slug' => 'oborudovanie',
        'status' => 'active',
        'description' => 'Описание оборудования',
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Оборудование');

    $this->assertDatabaseHas('categories', ['slug' => 'oborudovanie']);
});

test('можно обновить категорию', function () {
    $category = Category::factory()->create();

    $data = [
        'name' => 'Обновленное имя',
        'slug' => 'updated-slug',
        'status' => 'inactive',
    ];

    $response = $this->putJson("/api/v1/categories/{$category->id}", $data);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Обновленное имя');

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Обновленное имя']);
});

test('можно удалить категорию', function () {
    $category = Category::factory()->create();

    $response = $this->deleteJson("/api/v1/categories/{$category->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

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

test('валидация parent_id при создании категории', function () {
    $data = [
        'parent_id' => 999, // Несуществующий ID
        'name' => 'Дочерняя категория',
        'slug' => 'child-cat',
    ];

    $response = $this->postJson('/api/v1/categories', $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['parent_id']);
});
