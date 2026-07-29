<?php

namespace App\Application\Commands\Media;

use Spatie\MediaLibrary\HasMedia;

/**
 * Команда прикрепления изображения к модели по внешнему URL.
 *
 * Изображение приводится к квадрату $width x $height (по умолчанию 800x800).
 */
class AttachImageFromUrlCommand
{
    public function __construct(
        public HasMedia $model,
        public string $imageUrl,
        public string $collectionName = 'main',
        public ?string $fileName = null,
        public bool $clearCollection = false,
        public int $width = 800,
        public int $height = 800,
    ) {}
}
