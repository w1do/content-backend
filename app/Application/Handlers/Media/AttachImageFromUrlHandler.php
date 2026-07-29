<?php

namespace App\Application\Handlers\Media;

use App\Application\Commands\Media\AttachImageFromUrlCommand;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Image;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Скачивает изображение по URL, приводит его к единому размеру
 * и прикрепляет к медиа-коллекции модели.
 */
class AttachImageFromUrlHandler
{
    /**
     * Обрабатывает прикрепление изображения из URL к модели.
     *
     * @throws RuntimeException если изображение не удалось скачать
     */
    public function handle(AttachImageFromUrlCommand $command): Media
    {
        if ($command->clearCollection) {
            $command->model->clearMediaCollection($command->collectionName);
        }

        $temporaryPath = $this->downloadAndNormalize($command);

        try {
            $adder = $command->model->addMedia($temporaryPath);

            if ($command->fileName) {
                $adder->usingFileName($this->normalizeFileName($command->fileName));
            }

            return $adder->toMediaCollection($command->collectionName);
        } finally {
            if (is_file($temporaryPath)) {
                @unlink($temporaryPath);
            }
        }
    }

    /**
     * Скачивает файл во временную директорию и приводит его к квадрату заданного размера.
     */
    private function downloadAndNormalize(AttachImageFromUrlCommand $command): string
    {
        $response = Http::timeout(30)->get($command->imageUrl);

        if (! $response->successful()) {
            throw new RuntimeException("Не удалось скачать изображение: {$command->imageUrl}");
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'media_').'.jpg';
        file_put_contents($temporaryPath, $response->body());

        Image::load($temporaryPath)
            ->fit(Fit::Crop, $command->width, $command->height)
            ->format('jpg')
            ->quality(90)
            ->save($temporaryPath);

        return $temporaryPath;
    }

    /**
     * Приводит имя файла к расширению .jpg, так как изображение всегда конвертируется в JPEG.
     */
    private function normalizeFileName(string $fileName): string
    {
        return Str::of($fileName)
            ->beforeLast('.')
            ->whenEmpty(fn () => Str::of($fileName))
            ->append('.jpg')
            ->toString();
    }
}
