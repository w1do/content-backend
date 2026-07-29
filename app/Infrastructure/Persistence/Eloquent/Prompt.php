<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Domain\Enums\PromptCategory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property PromptCategory $category
 * @property string $rule
 * @property bool $status
 */
class Prompt extends Model
{
    protected $fillable = [
        'category',
        'rule',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'category' => PromptCategory::class,
            'status' => 'boolean',
        ];
    }
}
