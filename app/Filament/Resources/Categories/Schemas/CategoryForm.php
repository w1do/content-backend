<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Domain\Enums\PromptCategory;
use App\Filament\Actions\SearchImageAction;
use App\Filament\Actions\SeoGenerateAction;
use App\Filament\Forms\Components\CategorySelect;
use App\Filament\Resources\Seo\Schemas\SeoSchema;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
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
                                ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                ->hintAction(SearchImageAction::make('name')),

                            TextInput::make('slug')
                                ->label('Слаг')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                        ])->columns(2),

                        CategorySelect::make('parent_id')
                            ->label('Родительская категория')
                            ->relationship('parent', 'name')
                            ->placeholder('Выберите родительскую категорию (опционально)'),

                        RichEditor::make('description')
                            ->label('Описание')
                            ->columnSpanFull()
                            ->hintAction(SeoGenerateAction::make(PromptCategory::Category, 'name')),

                        ToggleButtons::make('status')
                            ->label('Статус')
                            ->options([
                                'active' => 'Активен',
                                'inactive' => 'Неактивен',
                            ])
                            ->default('active')
                            ->inline(),

                        SpatieMediaLibraryFileUpload::make('image')
                            ->label('Изображение')
                            ->collection('main')
                            ->image()
                            ->imageEditor(),

                        SeoSchema::getSeoSection(),
                    ])->columnSpanFull(),
            ]);
    }
}
