<?php

namespace App\Filament\Actions;

use App\Application\Jobs\GenerateSeoJob;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class SeoGenerateBulkAction
{
    public static function make(): BulkAction
    {
        return BulkAction::make('generate_seo')
            ->label('Сгенерировать SEO')
            ->icon('heroicon-m-sparkles')
            ->requiresConfirmation()
            ->action(function (Collection $records) {
                $count = 0;
                foreach ($records as $record) {
                    // Проверяем наличие SEO.
                    // Если у записи нет SEO или оно не заполнено (пустой заголовок и описание)
                    if (! $record->seo || (empty($record->seo->title) && empty($record->seo->description))) {
                        GenerateSeoJob::dispatch($record);
                        $count++;
                    }
                }

                if ($count > 0) {
                    Notification::make()
                        ->title('SEO генерация запущена')
                        ->body("Задачи для {$count} записей добавлены в очередь.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Генерация не требуется')
                        ->body('У всех выбранных записей уже есть SEO.')
                        ->info()
                        ->send();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}
