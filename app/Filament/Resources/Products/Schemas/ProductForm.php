<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Application\Handlers\Products\ParseProductFromUrlHandler;
use App\Application\Queries\Products\ParseProductFromUrlQuery;
use App\Domain\Enums\PromptCategory;
use App\Filament\Actions\SearchImageAction;
use App\Filament\Actions\SeoGenerateAction;
use App\Filament\Forms\Components\CategorySelect;
use App\Filament\Resources\Seo\Schemas\SeoSchema;
use App\Infrastructure\Persistence\Eloquent\Product;
use Filament\Actions\Action;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * Класс для конфигурации формы товара в Filament.
 * Реализует разделение полей по табам (Главное, Медиа, Сео, Подгрузка товара)
 * и использует Flex (Split) layout для выноса метаданных в боковую панель.
 */
class ProductForm
{
    /**
     * Конфигурирует схему формы товара.
     */
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Flex::make([
                    Group::make([
                        Tabs::make('Информация')
                            ->tabs([
                                Tab::make('Главное')
                                    ->schema([
                                        Group::make([
                                            TextInput::make('name')
                                                ->label('Наименование')
                                                ->required()
                                                ->maxLength(255)
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(fn (string $operation, $state, $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null)
                                                ->hintAction(SearchImageAction::make('name')),

                                            TextInput::make('slug')
                                                ->label('Слаг')
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->maxLength(255)
                                                ->unique(Product::class, 'slug', ignoreRecord: true),
                                        ])->columns(2),

                                        MarkdownEditor::make('description')
                                            ->label('Описание')
                                            ->columnSpanFull()
                                            ->hintAction(SeoGenerateAction::make(PromptCategory::Products, 'name')),

                                        KeyValue::make('attributes')
                                            ->label('Характеристики')
                                            ->columnSpanFull(),
                                    ]),

                                Tab::make('Медиа')
                                    ->schema([
                                        SpatieMediaLibraryFileUpload::make('image')
                                            ->label('Главное изображение')
                                            ->collection('main')
                                            ->image()
                                            ->imageEditor(),

                                        SpatieMediaLibraryFileUpload::make('gallery')
                                            ->label('Галерея')
                                            ->collection('gallery')
                                            ->multiple()
                                            ->reorderable()
                                            ->image()
                                            ->imageEditor()
                                            ->panelLayout('grid')
                                            ->columns(2),
                                    ]),

                                Tab::make('Сео')
                                    ->schema([
                                        SeoSchema::getSeoSection(),
                                    ]),

                                Tab::make('Подгрузка товара')
                                    ->schema([
                                        TextInput::make('external_url')
                                            ->label('Ссылка на товар (mirgaza.ru)')
                                            ->helperText('Вставьте ссылку на товар для автоматического заполнения полей')
                                            ->dehydrated(false)
                                            ->suffixAction(
                                                Action::make('parse')
                                                    ->label('Загрузить')
                                                    ->icon('heroicon-m-arrow-path')
                                                    ->action(function ($state, $set) {
                                                        if (blank($state)) {
                                                            Notification::make()
                                                                ->title('Введите ссылку')
                                                                ->warning()
                                                                ->send();

                                                            return;
                                                        }

                                                        try {
                                                            $handler = app(ParseProductFromUrlHandler::class);
                                                            $result = $handler->handle(new ParseProductFromUrlQuery($state));

                                                            $set('name', $result->name);
                                                            $set('slug', Str::slug($result->name));
                                                            $set('description', filled($result->description) ? $result->description : null);
                                                            $set('attributes', $result->attributes);

                                                            Notification::make()
                                                                ->title('Данные успешно загружены')
                                                                ->success()
                                                                ->send();
                                                        } catch (\Exception $e) {
                                                            Notification::make()
                                                                ->title('Ошибка при парсинге')
                                                                ->body($e->getMessage())
                                                                ->danger()
                                                                ->send();
                                                        }
                                                    }),
                                            ),
                                    ]),
                            ]),
                    ])->grow(),

                    Section::make()
                        ->schema([
                            CategorySelect::make('categories')
                                ->relationship('categories', 'name')
                                ->multiple()
                                ->preload(),
                        ])
                        ->grow(false),
                ])->columnSpanFull()->from('lg')->from('lg'),
            ]);
    }
}
