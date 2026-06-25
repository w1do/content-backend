<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Filament\Forms\Components\CategorySelect;
use App\Infrastructure\Persistence\Eloquent\Product;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Group::make([
                            TextInput::make('name')
                                ->label('Наименование')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->label('Слаг')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255)
                                ->unique(Product::class, 'slug', ignoreRecord: true),
                        ])->columns(2),

                        CategorySelect::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->preload(),

                        RichEditor::make('description')
                            ->label('Описание')
                            ->columnSpanFull(),

                        KeyValue::make('attributes')
                            ->label('Характеристики')
                            ->columnSpanFull(),

                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Главное изображение')
                            ->collection('main')
                            ->image()
                            ->imageEditor(),

                        SpatieMediaLibraryFileUpload::make('gallery')
                            ->label('Галерея')
                            ->collection('gallery')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->imageEditor()
                            ->panelLayout('grid')
                            ->columns(2),
                    ])->columnSpanFull(),
            ]);
    }
}
