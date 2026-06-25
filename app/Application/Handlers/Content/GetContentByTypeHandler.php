<?php

namespace App\Application\Handlers\Content;

use App\Application\Queries\Content\GetContentByTypeQuery;
use App\Domain\Entities\Content;
use App\Domain\Repositories\ContentRepositoryInterface;

class GetContentByTypeHandler
{
    public function __construct(
        private ContentRepositoryInterface $repository
    ) {}

    /**
     * @return Content[]
     */
    public function handle(GetContentByTypeQuery $query): array
    {
        return $this->repository->findByType($query->type);
    }
}
