<?php

namespace App\Filament\Resources\Prompts\Tables;

use App\Application\Services\PromptService;
use App\Infrastructure\Persistence\Eloquent\Prompt;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category')
                    ->label('Категория')
                    ->badge()
                    ->sortable(),
                TextColumn::make('rule')
                    ->label('Правило')
                    ->limit(50)
                    ->searchable(),
                IconColumn::make('status')
                    ->label('Активен')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Обновлено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('improve')
                    ->label('Улучшить prompt')
                    ->icon('heroicon-o-sparkles')
                    ->color('success')
                    ->action(function (Prompt $record, PromptService $promptService) {
                        try {
                            $improvedPrompt = $promptService->improvePrompt($record->rule);
                            $record->update(['rule' => $improvedPrompt]);

                            Notification::make()
                                ->title('Промпт успешно улучшен')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Ошибка при улучшении промпта')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Улучшить промпт?')
                    ->modalDescription('Система проанализирует текущий markdown и предложит улучшенную версию. Это действие изменит текущее правило.')
                    ->modalSubmitActionLabel('Улучшить'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
