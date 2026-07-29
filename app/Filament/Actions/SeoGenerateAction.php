<?php

namespace App\Filament\Actions;

use App\Application\Services\SeoGenerator;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SeoGenerateAction
{
    public static function make(): Action
    {
        return Action::make('generate_seo')
            ->label('Сгенерировать SEO')
            ->icon('heroicon-m-sparkles')
            ->action(function ($state, $set) {
                if (blank($state)) {
                    Notification::make()
                        ->title('Поле пустое')
                        ->body('Заполните описание, чтобы сгенерировать SEO.')
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    $generator = app(SeoGenerator::class);
                    $result = $generator->generateFromText(strip_tags($state));

                    if ($result) {
                        $set('seo.title', $result['title']);
                        $set('seo.description', $result['description']);

                        Notification::make()
                            ->title('SEO успешно сгенерировано')
                            ->success()
                            ->send();
                    } else {
                        throw new \Exception('Не удалось получить ответ от AI.');
                    }
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Ошибка генерации')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
