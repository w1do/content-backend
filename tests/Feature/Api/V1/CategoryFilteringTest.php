<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_categories_by_id(): void
    {
        $categories = Category::factory()->count(3)->create();
        $target = $categories->first();

        $response = $this->getJson("/api/v1/categories?filter[id]={$target->id}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $target->id);
    }

    public function test_can_filter_categories_by_multiple_ids(): void
    {
        $categories = Category::factory()->count(3)->create();
        $targets = $categories->take(2);
        $ids = $targets->pluck('id')->implode(',');

        $response = $this->getJson("/api/v1/categories?filter[id]={$ids}");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_categories_by_partial_name(): void
    {
        Category::factory()->create(['name' => 'Электроника']);
        Category::factory()->create(['name' => 'Бытовая техника']);
        Category::factory()->create(['name' => 'Одежда']);

        $response = $this->getJson('/api/v1/categories?filter[name]=ника');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_categories_by_exact_slug(): void
    {
        Category::factory()->create(['name' => 'Электроника', 'slug' => 'electronics']);
        Category::factory()->create(['name' => 'Электрика', 'slug' => 'electrics']);

        $response = $this->getJson('/api/v1/categories?filter[slug]=electronics');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'electronics');
    }

    public function test_can_filter_categories_by_full_path(): void
    {
        $root = Category::factory()->create(['name' => 'Оборудование', 'slug' => 'oborudovanie']);
        $child = Category::factory()->create(['name' => 'Газовое', 'slug' => 'gazovoe', 'parent_id' => $root->id]);

        $child->refresh(); // Убеждаемся, что full_path сгенерирован

        $response = $this->getJson("/api/v1/categories?filter[full_path]={$child->full_path}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.full_path', $child->full_path);
    }

    public function test_cannot_filter_categories_by_partial_full_path(): void
    {
        $root = Category::factory()->create(['name' => 'Оборудование', 'slug' => 'oborudovanie']);
        Category::factory()->create(['name' => 'Газовое', 'slug' => 'gazovoe', 'parent_id' => $root->id]);

        // Пытаемся найти по части пути (например, только родительский слаг)
        $response = $this->getJson('/api/v1/categories?filter[full_path]=oborudovanie');

        // Должен найтись только корень, если у него full_path == 'oborudovanie',
        // но НЕ дочерний 'oborudovanie/gazovoe'
        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $category) {
            $this->assertEquals('oborudovanie', $category['full_path']);
        }
    }

    public function test_can_combine_filters(): void
    {
        Category::factory()->create(['name' => 'Котел газовый', 'slug' => 'kotel-gazoviy']);
        Category::factory()->create(['name' => 'Котел электрический', 'slug' => 'kotel-electric']);

        $response = $this->getJson('/api/v1/categories?filter[name]=Котел&filter[slug]=kotel-gazoviy');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', 'kotel-gazoviy');
    }
}
