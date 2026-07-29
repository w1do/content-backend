<?php

namespace App\Filament\Resources\Prompts\Schemas;

use App\Domain\Enums\PromptCategory;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PromptForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category')
                    ->label('Категория')
                    ->options(PromptCategory::class)
                    ->required()
                    ->unique(ignoreRecord: true),
                MarkdownEditor::make('rule')
                    ->label('Правило (Markdown)')
                    ->required()
                    ->columnSpanFull(),
                Toggle::make('status')
                    ->label('Активен')
                    ->default(true),
            ]);
    }
}
