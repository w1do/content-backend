<?php

namespace App\Filament\Actions;

use App\Application\Commands\Media\AttachImageFromUrlCommand;
use App\Application\Handlers\Media\AttachImageFromUrlHandler;
use App\Domain\Services\ImageSearchProviderInterface;
use Filament\Actions\Action;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SearchImageAction
{
    protected static array $cache = [];

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
            ->color('primary')
            ->modalHeading('Выбор изображения')
            ->modalSubmitActionLabel('Сохранить')
            ->modalWidth('6xl')
            ->form(function ($get) use ($searchField) {
                $query = $get($searchField);
                $images = [];

                if (blank(config('services.serp_api.key'))) {
                    Notification::make()
                        ->title('Ошибка конфигурации')
                        ->body('Ключ SERP_API не найден. Проверьте настройки.')
                        ->danger()
                        ->send();
                }

                if (filled($query)) {
                    try {
                        $images = static::getSearchResults($query);
                    } catch (\Exception $e) {
                        Log::error('SearchImageAction form search error: '.$e->getMessage());
                    }
                }

                return [
                    ViewField::make('search_image_url')
                        ->label('Выберите изображение')
                        ->view('filament.components.image-picker')
                        ->viewData([
                            'images' => $images,
                        ])
                        ->required()
                        ->columnSpanFull(),
                ];
            })
            ->mountUsing(function ($get, $set) use ($searchField) {
                $query = $get($searchField);

                if (blank($query)) {
                    return;
                }

                try {
                    $results = static::getSearchResults($query);

                    if (! empty($results)) {
                        $set('search_image_url', $results[0]->url);
                    }
                } catch (\Exception $e) {
                    Log::error('SearchImageAction mount error: '.$e->getMessage());
                }
            })
            ->action(function (array $data, $record, $livewire) {
                $imageUrl = $data['search_image_url'] ?? null;

                if (empty($imageUrl)) {
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

                    // Generate a clean filename
                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    // Strip query parameters from extension if any
                    $extension = explode('?', $extension)[0];
                    if (empty($extension) || strlen($extension) > 4) {
                        $extension = 'jpg';
                    }

                    $fileName = Str::slug($record->name).'.'.$extension;

                    $handler->handle(new AttachImageFromUrlCommand(
                        model: $record,
                        imageUrl: $imageUrl,
                        collectionName: 'main',
                        fileName: $fileName,
                        clearCollection: true
                    ));

                    $record->refresh();
                    $record->load('media');

                    if (method_exists($livewire, 'refreshFormData')) {
                        $livewire->refreshFormData(['image', 'name']);
                    } else {
                        $livewire->refresh();
                    }

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

    protected static function getSearchResults(string $query): array
    {
        if (isset(static::$cache[$query])) {
            return static::$cache[$query];
        }

        $provider = app(ImageSearchProviderInterface::class);
        $results = $provider->search($query);

        return static::$cache[$query] = $results;
    }
}
