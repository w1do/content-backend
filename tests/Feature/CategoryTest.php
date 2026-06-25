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
