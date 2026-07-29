<?php

namespace App\Infrastructure\Persistence\Repositories\Cached;

use App\Application\DTO\CategoryFilterDTO;
use App\Domain\Entities\Category;
use App\Domain\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Caching\CacheServiceInterface;
use App\Infrastructure\Caching\KeyGenerator;

class CachedCategoryRepository implements CategoryRepositoryInterface
{
    private string $entity = 'category';

    public function __construct(
        private CategoryRepositoryInterface $repository,
        private CacheServiceInterface $cache,
        private KeyGenerator $keyGenerator
    ) {}

    /**
     * {@inheritDoc}
     */
    public function findAll(?CategoryFilterDTO $filters = null): array
    {
        if ($filters === null) {
            return $this->cache->remember(
                $this->keyGenerator->generate($this->entity, __FUNCTION__),
                [$this->getTags()],
                fn () => $this->repository->findAll(),
                config('caching.ttls.category')
            );
        }

        return $this->repository->findAll($filters);
    }

    /**
     * {@inheritDoc}
     */
    public function findTree(): array
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__),
            [$this->getTags()],
            fn () => $this->repository->findTree(),
            config('caching.ttls.category')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Category
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__, [$id]),
            [$this->getTags()],
            fn () => $this->repository->findById($id),
            config('caching.ttls.category')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findBySlug(string $slug): ?Category
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__, [$slug]),
            [$this->getTags()],
            fn () => $this->repository->findBySlug($slug),
            config('caching.ttls.category')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(Category $category): Category
    {
        $saved = $this->repository->save($category);
        $this->cache->invalidate([$this->getTags()]);

        return $saved;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(int $id): bool
    {
        $deleted = $this->repository->delete($id);
        $this->cache->invalidate([$this->getTags()]);

        return $deleted;
    }

    /**
     * {@inheritDoc}
     */
    public function getAncestors(int $id): array
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__, [$id]),
            [$this->getTags()],
            fn () => $this->repository->getAncestors($id),
            config('caching.ttls.category')
        );
    }

    private function getTags(): string
    {
        return (string) config('caching.tags.category', 'categories');
    }
}
