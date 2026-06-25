<?php

namespace App\Filament\Forms\Components;

use App\Infrastructure\Persistence\Eloquent\Category;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;

class CategorySelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Категории')
            ->searchable()
            ->options(function (?Model $record) {
                return Category::query()
                    ->when($record instanceof Category, function ($query) use ($record) {
                        return $query->whereNotDescendantOf($record)->where('id', '!=', $record->id);
                    })
                    ->withDepth()
                    ->defaultOrder()
                    ->get()
                    ->mapWithKeys(fn (Category $category) => [
                        $category->id => str_repeat('- ', $category->depth).$category->name,
                    ]);
            });
    }
}
