<?php

use App\Domain\Enums\PromptCategory;
use App\Infrastructure\Persistence\Eloquent\Prompt;
use App\Infrastructure\Persistence\Repositories\EloquentPromptRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can fetch an active prompt by category', function () {
    Prompt::create([
        'category' => PromptCategory::Products,
        'rule' => 'Test Rule',
        'status' => true,
    ]);

    $repository = new EloquentPromptRepository;
    $prompt = $repository->getActiveByCategory(PromptCategory::Products);

    expect($prompt)->not->toBeNull();
    expect($prompt->category)->toBe(PromptCategory::Products);
    expect($prompt->rule)->toBe('Test Rule');
});

test('it returns null if prompt is inactive', function () {
    Prompt::create([
        'category' => PromptCategory::Products,
        'rule' => 'Test Rule',
        'status' => false,
    ]);

    $repository = new EloquentPromptRepository;
    $prompt = $repository->getActiveByCategory(PromptCategory::Products);

    expect($prompt)->toBeNull();
});

test('it returns null if no prompt exists for category', function () {
    $repository = new EloquentPromptRepository;
    $prompt = $repository->getActiveByCategory(PromptCategory::Products);

    expect($prompt)->toBeNull();
});
