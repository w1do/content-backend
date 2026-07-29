<?php

namespace App\Application\Commands\Media;

use Spatie\MediaLibrary\HasMedia;

class AttachImageFromUrlCommand
{
    public function __construct(
        public HasMedia $model,
        public string $imageUrl,
        public string $collectionName = 'main',
        public ?string $fileName = null,
    ) {}
}
