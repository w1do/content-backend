<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Infrastructure\Persistence\Eloquent\Category;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Components\ToggleButtons;
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
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                            TextInput::make('slug')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255)
                                ->unique(Category::class, 'slug', ignoreRecord: true),
                        ])->columns(2),

                        Select::make('parent_id')
                            ->label('Parent Category')
                            ->relationship('parent', 'name')
                            ->options(Category::all()->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Select a parent category (optional)'),

                        RichEditor::make('description')
                            ->columnSpanFull(),

                        ToggleButtons::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->inline(),
                    ]),
            ]);
    }
}
