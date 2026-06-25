<?php

namespace App\Filament\Resources\Contents\Pages;

use App\Domain\Enums\ContentType;
use App\Filament\Resources\Contents\ContentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListContents extends ListRecords
{
    protected static string $resource = ContentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Все'),
            'blog' => Tab::make('Посты')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', ContentType::Blog)),
            'page' => Tab::make('Страницы')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', ContentType::Page)),
            'system' => Tab::make('Системные страницы')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', ContentType::System)),
            'material' => Tab::make('Материалы')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('type', ContentType::Material)),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return request()->query('tab');
    }
}
