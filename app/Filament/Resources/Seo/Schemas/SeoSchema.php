<?php

namespace App\Filament\Resources\Seo\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

class SeoSchema
{
    public static function getSeoSection(): Section
    {
        return Section::make('SEO')
            ->description('Управление поисковой оптимизацией')
            ->collapsible()
            ->collapsed()
            ->relationship('seo')
            ->schema([
                TextInput::make('title')
                    ->label('SEO Заголовок')
                    ->maxLength(255)
                    ->helperText('Рекомендуется до 60 символов')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('SEO Описание')
                    ->rows(3)
                    ->maxLength(255)
                    ->helperText('Рекомендуется до 160 символов')
                    ->columnSpanFull(),

                Toggle::make('is_indexable')
                    ->label('Индексировать поисковыми системами')
                    ->default(true),

                SpatieMediaLibraryFileUpload::make('image')
                    ->label('SEO Изображение')
                    ->collection('image')
                    ->image()
                    ->imageEditor()
                    ->columnSpanFull(),
            ]);
    }
}
