<?php

namespace App\Filament\Resources\Seo\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SeosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label('Изображение')
                    ->collection('image'),

                TextColumn::make('title')
                    ->label('SEO Заголовок')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('seoable_type')
                    ->label('Тип сущности')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'App\Infrastructure\Persistence\Eloquent\Product' => 'Товар',
                        'App\Infrastructure\Persistence\Eloquent\Category' => 'Категория',
                        'App\Infrastructure\Persistence\Eloquent\Content' => 'Контент',
                        default => $state,
                    })
                    ->badge()
                    ->sortable(),

                TextColumn::make('seoable_name')
                    ->label('Сущность')
                    ->getStateUsing(function ($record) {
                        return $record->seoable?->name ?? $record->seoable?->title ?? 'N/A';
                    }),

                IconColumn::make('is_indexable')
                    ->label('Индексация')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
