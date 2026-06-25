<?php

namespace App\Domain\Enums;

enum ContentType: string
{
    case Blog = 'blog';
    case Page = 'page';
    case System = 'system';
    case Material = 'material';

    public function getLabel(): string
    {
        return match ($this) {
            self::Blog => 'Пост',
            self::Page => 'Страница',
            self::System => 'Системная страница',
            self::Material => 'Материал',
        };
    }
}
