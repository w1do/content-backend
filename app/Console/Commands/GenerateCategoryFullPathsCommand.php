<?php

namespace App\Console\Commands;

use App\Infrastructure\Persistence\Eloquent\Category;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('categories:generate-full-paths')]
#[Description('Generate and persist the full_path for all existing categories')]
class GenerateCategoryFullPathsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $categories = Category::defaultOrder()->get();

        $this->components->info("Generating full_path for {$categories->count()} categories...");

        $bar = $this->output->createProgressBar($categories->count());
        $bar->start();

        /** @var Category $category */
        foreach ($categories as $category) {
            $category->full_path = $category->generateFullPath();
            $category->saveQuietly();

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->components->info('Done.');

        return self::SUCCESS;
    }
}
