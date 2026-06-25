<?php

namespace App\Filament\Resources\Contents;

use App\Filament\Resources\Contents\Pages\CreateContent;
use App\Filament\Resources\Contents\Pages\EditContent;
use App\Filament\Resources\Contents\Pages\ListContents;
use App\Filament\Resources\Contents\Schemas\ContentForm;
use App\Filament\Resources\Contents\Tables\ContentsTable;
use App\Infrastructure\Persistence\Eloquent\Content;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentResource extends Resource
{
    protected static ?string $model = Content::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getModelLabel(): string
    {
        return 'Контент';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Контент';
    }

    public static function getNavigationItems(): array
    {
        return [
            NavigationItem::make('Посты')
                ->group('Контент')
                ->icon('heroicon-o-document-text')
                ->isActiveWhen(fn () => request()->query('tab') === 'blog')
                ->url(static::getUrl('index', ['tab' => 'blog'])),
            NavigationItem::make('Страницы')
                ->group('Контент')
                ->icon('heroicon-o-document')
                ->isActiveWhen(fn () => request()->query('tab') === 'page')
                ->url(static::getUrl('index', ['tab' => 'page'])),
            NavigationItem::make('Системные страницы')
                ->group('Контент')
                ->icon('heroicon-o-shield-check')
                ->isActiveWhen(fn () => request()->query('tab') === 'system')
                ->url(static::getUrl('index', ['tab' => 'system'])),
            NavigationItem::make('Материалы')
                ->group('Контент')
                ->icon('heroicon-o-rectangle-stack')
                ->isActiveWhen(fn () => request()->query('tab') === 'material')
                ->url(static::getUrl('index', ['tab' => 'material'])),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ContentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContents::route('/'),
            'create' => CreateContent::route('/create'),
            'edit' => EditContent::route('/{record}/edit'),
        ];
    }
}
