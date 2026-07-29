<?php

namespace App\Application\Jobs;

use App\Application\Services\SeoGenerator;
use App\Domain\Entities\Seo as SeoEntity;
use App\Domain\Repositories\SeoRepositoryInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSeoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly Model $model
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SeoGenerator $seoGenerator, SeoRepositoryInterface $seoRepository): void
    {
        // Check if SEO already exists and has title or description
        $existingSeo = $seoRepository->findBySeoable($this->model->getMorphClass(), $this->model->id);

        if ($existingSeo && ($existingSeo->title || $existingSeo->description)) {
            return;
        }

        // Extract text for generation: description (stripped) or name as fallback
        $text = ! empty($this->model->description)
            ? strip_tags($this->model->description)
            : $this->model->name;

        if (empty($text)) {
            return;
        }

        // Generate SEO using the AI service
        $generated = $seoGenerator->generateFromText($text);

        if (! $generated) {
            return;
        }

        // Create or update SEO entity.
        // We preserve manual input if only one field was filled (though handle returns early if either is set).
        $seo = new SeoEntity(
            id: $existingSeo?->id,
            seoableType: $this->model->getMorphClass(),
            seoableId: $this->model->id,
            title: $existingSeo?->title ?: $generated['title'],
            description: $existingSeo?->description ?: $generated['description'],
            isIndexable: $existingSeo?->isIndexable ?? true,
            meta: $existingSeo?->meta ?? [],
        );

        $seoRepository->save($seo);
    }
}
