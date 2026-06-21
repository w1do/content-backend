<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class EntityNotFoundException extends DomainException
{
    public function __construct(string $entity, string $id)
    {
        parent::__construct(sprintf('Entity %s with ID %s not found.', $entity, $id), 404);
    }
}
