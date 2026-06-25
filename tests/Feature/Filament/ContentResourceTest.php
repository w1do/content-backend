<?php

use App\Domain\Enums\ContentType;
use App\Filament\Resources\Contents\Pages\ListContents;
use App\Infrastructure\Persistence\Eloquent\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('can filter contents by type via tab url parameter', function () {
    Content::factory()->create(['type' => ContentType::Blog, 'name' => 'Blog Post']);
    Content::factory()->create(['type' => ContentType::Page, 'name' => 'Static Page']);

    // Test Blog tab
    Livewire::withQueryParams(['tab' => 'blog'])
        ->test(ListContents::class)
        ->assertCanSeeTableRecords(Content::where('type', ContentType::Blog)->get())
        ->assertCanNotSeeTableRecords(Content::where('type', ContentType::Page)->get());

    // Test Page tab
    Livewire::withQueryParams(['tab' => 'page'])
        ->test(ListContents::class)
        ->assertCanSeeTableRecords(Content::where('type', ContentType::Page)->get())
        ->assertCanNotSeeTableRecords(Content::where('type', ContentType::Blog)->get());
});
