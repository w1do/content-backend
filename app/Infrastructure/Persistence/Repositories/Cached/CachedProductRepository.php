<?php

namespace App\Infrastructure\Persistence\Repositories\Cached;

use App\Application\DTO\ProductFilterDTO;
use App\Domain\Entities\Product;
use App\Domain\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Caching\CacheServiceInterface;
use App\Infrastructure\Caching\KeyGenerator;

class CachedProductRepository implements ProductRepositoryInterface
{
    private string $entity = 'product';

    public function __construct(
        private ProductRepositoryInterface $repository,
        private CacheServiceInterface $cache,
        private KeyGenerator $keyGenerator
    ) {}

    /**
     * {@inheritDoc}
     */
    public function findAll(?ProductFilterDTO $filters = null): array
    {
        if ($filters === null) {
            return $this->cache->remember(
                $this->keyGenerator->generate($this->entity, __FUNCTION__),
                [$this->getTags()],
                fn () => $this->repository->findAll(),
                config('caching.ttls.product')
            );
        }

        return $this->repository->findAll($filters);
    }

    /**
     * {@inheritDoc}
     */
    public function findByCategories(array $categoryIds): array
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__, [$categoryIds]),
            [$this->getTags()],
            fn () => $this->repository->findByCategories($categoryIds),
            config('caching.ttls.product')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function findById(int $id): ?Product
    {
        return $this->cache->remember(
            $this->keyGenerator->generate($this->entity, __FUNCTION__, [$id]),
            [$this->getTags()],
            fn () => $this->repository->findById($id),
            config('caching.ttls.product')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(Product $product): Product
    {
        $saved = $this->repository->save($product);
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

    private function getTags(): string
    {
        return (string) config('caching.tags.product', 'products');
    }
}
