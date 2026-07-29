<?php

namespace App\Filament\Resources\Seo\Pages;

use App\Domain\Enums\ContentType;
use App\Filament\Resources\Seo\SeoResource;
use App\Infrastructure\Persistence\Eloquent\Content;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSeos extends ListRecords
{
    protected static string $resource = SeoResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все'),
            'categories' => Tab::make('Категории')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('seoable_type', 'App\Infrastructure\Persistence\Eloquent\Category')),
            'products' => Tab::make('Товары')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('seoable_type', 'App\Infrastructure\Persistence\Eloquent\Product')),
            'blog' => Tab::make('Блог')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('seoable_type', 'App\Infrastructure\Persistence\Eloquent\Content')
                    ->whereHasMorph('seoable', [Content::class], function ($query) {
                        $query->where('type', ContentType::Blog);
                    })
                ),
        ];
    }
}
