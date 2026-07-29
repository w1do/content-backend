<?php

namespace App\Filament\Actions;

use App\Application\Services\SeoGenerator;
use App\Domain\Enums\PromptCategory;
use Closure;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SeoGenerateAction
{
    /**
     * Create a new SEO generate action.
     *
     * @param  PromptCategory|Closure  $category  The category for SEO rules.
     * @param  string|Closure|null  $titleField  The name of the field containing the entity title (e.g., 'name' or 'title').
     */
    public static function make(PromptCategory|Closure $category = PromptCategory::General, string|Closure|null $titleField = null): Action
    {
        return Action::make('generate_seo')
            ->label('Сгенерировать SEO')
            ->icon('heroicon-m-sparkles')
            ->action(function ($state, $set, $get, Action $action) use ($category, $titleField) {
                if (blank($state)) {
                    Notification::make()
                        ->title('Поле пустое')
                        ->body('Заполните описание, чтобы сгенерировать SEO.')
                        ->warning()
                        ->send();

                    return;
                }

                try {
                    $resolvedCategory = $category instanceof Closure ? $action->evaluate($category) : $category;
                    $resolvedTitleField = $titleField instanceof Closure ? $action->evaluate($titleField) : $titleField;

                    $context = [];
                    if ($resolvedTitleField) {
                        $context['title'] = $get($resolvedTitleField);
                    }

                    $generator = app(SeoGenerator::class);
                    $result = $generator->generateFromText(strip_tags($state), $resolvedCategory, $context);

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
