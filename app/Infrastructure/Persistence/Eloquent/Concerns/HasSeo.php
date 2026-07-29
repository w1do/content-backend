<?php

namespace App\Infrastructure\Persistence\Eloquent\Concerns;

use App\Infrastructure\Persistence\Eloquent\Seo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public function seo(): MorphOne
    {
        return $this->morphOne(Seo::class, 'seoable');
    }
}
