<?php

namespace App\Filament\Resources\Seo;

use App\Filament\Resources\Seo\Pages\ListSeos;
use App\Filament\Resources\Seo\Tables\SeosTable;
use App\Infrastructure\Persistence\Eloquent\Seo;
use BackedEnum;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeoResource extends Resource
{
    protected static ?string $model = Seo::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    public static function getModelLabel(): string
    {
        return __('SEO');
    }

    public static function getPluralModelLabel(): string
    {
        return __('SEO');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Детали SEO')
                ->schema([
                    TextInput::make('title')
                        ->label('SEO Заголовок')
                        ->maxLength(255),
                    Textarea::make('description')
                        ->label('SEO Описание')
                        ->rows(3)
                        ->maxLength(255),
                    Toggle::make('is_indexable')
                        ->label('Индексировать'),
                    SpatieMediaLibraryFileUpload::make('image')
                        ->label('SEO Изображение')
                        ->collection('image'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return SeosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeos::route('/'),
        ];
    }
}
