<?php

namespace Database\Seeders;

use App\Infrastructure\Persistence\Eloquent\Category;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $equipment = Category::where('slug', 'oborudovanie')->first();
        $gasEquipment = Category::where('slug', 'gazovoe-oborudovanie')->first();
        $cylinders = Category::where('slug', 'ballony')->first();
        $parts = Category::where('slug', 'zapchasti')->first();

        if ($equipment) {
            Product::create([
                'name' => 'Универсальное оборудование',
                'slug' => 'universal-equipment',
                'description' => 'Описание универсального оборудования',
            ])->categories()->attach($equipment);
        }

        if ($gasEquipment) {
            Product::create([
                'name' => 'Газовый котел',
                'slug' => 'gas-boiler',
                'description' => 'Мощный газовый котел',
            ])->categories()->attach($gasEquipment);
        }

        if ($cylinders) {
            Product::create([
                'name' => 'Баллон 50л',
                'slug' => 'cylinder-50l',
                'description' => 'Пропановый баллон 50 литров',
            ])->categories()->attach($cylinders);

            Product::create([
                'name' => 'Баллон 27л',
                'slug' => 'cylinder-27l',
                'description' => 'Пропановый баллон 27 литров',
            ])->categories()->attach($cylinders);
        }

        if ($parts) {
            Product::create([
                'name' => 'Редуктор газовый',
                'slug' => 'gas-regulator',
                'description' => 'Редуктор для баллона',
            ])->categories()->attach($parts);
        }
    }
}
