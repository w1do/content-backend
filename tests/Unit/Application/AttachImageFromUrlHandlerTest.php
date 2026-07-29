<?php

use App\Application\Commands\Media\AttachImageFromUrlCommand;
use App\Application\Handlers\Media\AttachImageFromUrlHandler;
use App\Infrastructure\Persistence\Eloquent\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Image\Image;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function fakeJpegBytes(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 120, 160, 200));

    ob_start();
    imagejpeg($image);
    $bytes = ob_get_clean();
    imagedestroy($image);

    return $bytes;
}

test('it attaches image from url resized to 800x800', function () {
    Http::fake([
        'example.com/*' => Http::response(fakeJpegBytes(1200, 400), 200),
    ]);

    $product = Product::factory()->create();

    $media = (new AttachImageFromUrlHandler)->handle(new AttachImageFromUrlCommand(
        model: $product,
        imageUrl: 'https://example.com/photo.png',
        collectionName: 'main',
        fileName: 'test-product.png',
        clearCollection: true,
    ));

    expect($media->file_name)->toBe('test-product.jpg');

    $image = Image::load($media->getPath());

    expect($image->getWidth())->toBe(800)
        ->and($image->getHeight())->toBe(800);
});

test('it throws when image cannot be downloaded', function () {
    Http::fake([
        'example.com/*' => Http::response('', 404),
    ]);

    $product = Product::factory()->create();

    expect(fn () => (new AttachImageFromUrlHandler)->handle(new AttachImageFromUrlCommand(
        model: $product,
        imageUrl: 'https://example.com/missing.jpg',
    )))->toThrow(RuntimeException::class);
});
