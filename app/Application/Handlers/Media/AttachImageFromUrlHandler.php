<?php

namespace App\Application\Handlers\Media;

use App\Application\Commands\Media\AttachImageFromUrlCommand;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AttachImageFromUrlHandler
{
    /**
     * Handles the attachment of an image from a URL to a model.
     */
    public function handle(AttachImageFromUrlCommand $command): Media
    {
        if ($command->clearCollection) {
            $command->model->clearMediaCollection($command->collectionName);
        }

        $adder = $command->model->addMediaFromUrl($command->imageUrl);

        if ($command->fileName) {
            $adder->usingFileName($command->fileName);
        }

        return $adder->toMediaCollection($command->collectionName);
    }
}
