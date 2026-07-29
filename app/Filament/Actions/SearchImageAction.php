<?php

namespace App\Filament\Actions;

use App\Application\Commands\Media\AttachImageFromUrlCommand;
use App\Application\Handlers\Media\AttachImageFromUrlHandler;
use App\Domain\Services\ImageSearchProviderInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class SearchImageAction
{
    /**
     * Create a new search image action.
     *
     * @param  string  $searchField  The name of the field containing the query (e.g., 'name').
     */
    public static function make(string $searchField = 'name'): Action
    {
        return Action::make('search_image')
            ->label('Подобрать изображение')
            ->icon('heroicon-m-magnifying-glass')
            ->modalHeading('Предварительный просмотр изображения')
            ->modalSubmitActionLabel('Сохранить')
            ->modalWidth('lg')
            ->form([
                Hidden::make('search_image_url'),
                Hidden::make('search_image_title'),
                Hidden::make('search_image_source'),
            ])
            ->mountUsing(function ($get, $set) use ($searchField) {
                $query = $get($searchField);

                if (blank($query)) {
                    return;
                }

                try {
                    $provider = app(ImageSearchProviderInterface::class);
                    $result = $provider->search($query);

                    if ($result) {
                        $set('search_image_url', $result->url);
                        $set('search_image_title', $result->title);
                        $set('search_image_source', $result->source);
                    }
                } catch (\Exception $e) {
                    Log::error('SearchImageAction error: '.$e->getMessage());
                }
            })
            ->modalContent(fn ($get) => view('filament.components.image-preview', [
                'imageUrl' => $get('search_image_url'),
                'title' => $get('search_image_title'),
                'source' => $get('search_image_source'),
            ]))
            ->action(function (array $data, $record) {
                if (empty($data['search_image_url'])) {
                    Notification::make()
                        ->title('Изображение не выбрано')
                        ->warning()
                        ->send();

                    return;
                }

                if (! $record) {
                    Notification::make()
                        ->title('Ошибка')
                        ->body('Сначала сохраните запись, чтобы прикрепить изображение.')
                        ->danger()
                        ->send();

                    return;
                }

                try {
                    $handler = app(AttachImageFromUrlHandler::class);
                    $handler->handle(new AttachImageFromUrlCommand(
                        model: $record,
                        imageUrl: $data['search_image_url'],
                        collectionName: 'main'
                    ));

                    Notification::make()
                        ->title('Изображение успешно сохранено')
                        ->success()
                        ->send();
                } catch (\Exception $e) {
                    Log::error('SearchImageAction save error: '.$e->getMessage());
                    Notification::make()
                        ->title('Ошибка при сохранении')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
