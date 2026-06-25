<?php

namespace App\Filament\Resources\Contents\Schemas;

use App\Domain\Enums\ContentType;
use App\Infrastructure\Persistence\Eloquent\Content;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ContentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Group::make([
                            Select::make('type')
                                ->label('Тип контента')
                                ->options(ContentType::class)
                                ->required()
                                ->native(false),

                            TextInput::make('name')
                                ->label('Заголовок')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        ])->columns(2),

                        Group::make([
                            TextInput::make('slug')
                                ->label('Слаг')
                                ->disabled()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255)
                                ->unique(Content::class, 'slug', ignoreRecord: true),

                            Select::make('category_id')
                                ->label('Категория')
                                ->relationship('category', 'name')
                                ->searchable()
                                ->preload(),
                        ])->columns(2),

                        Textarea::make('short_text')
                            ->label('Краткое описание')
                            ->rows(3)
                            ->columnSpanFull(),

                        RichEditor::make('full_text')
                            ->label('Полный текст')
                            ->columnSpanFull(),

                        Group::make([
                            TagsInput::make('tags')
                                ->label('Теги'),

                            TextInput::make('views')
                                ->label('Просмотры')
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                        ])->columns(2),
                    ])->columnSpanFull(),
            ]);
    }
}
