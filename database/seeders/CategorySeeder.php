<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = Category::create([
            'name' => 'Оборудование',
            'slug' => 'oborudovanie',
            'status' => 'active',
            'description' => 'Все виды оборудования',
        ]);

        $gasEquipment = Category::create([
            'name' => 'Газовое оборудование',
            'slug' => 'gazovoe-oborudovanie',
            'status' => 'active',
            'description' => 'Оборудование для работы с газом',
        ]);

        $cylinders = Category::create([
            'name' => 'Баллоны',
            'slug' => 'ballony',
            'status' => 'active',
            'description' => 'Газовые баллоны различных типов',
        ]);

        $parts = Category::create([
            'name' => 'Запчасти',
            'slug' => 'zapchasti',
            'status' => 'active',
            'description' => 'Запасные части для оборудования',
        ]);

        // Установка связей вручную через update
        Category::where('id', $gasEquipment->id)->update(['parent_id' => $equipment->id]);
        Category::where('id', $cylinders->id)->update(['parent_id' => $gasEquipment->id]);

        Category::fixTree();
    }
}
