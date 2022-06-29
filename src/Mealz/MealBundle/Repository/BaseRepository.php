<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template T of object
 * @template-implements ObjectRepository<T>
 */
abstract class BaseRepository implements ObjectRepository
{
    private string $entityClass;

    /**
     * @psalm-var ObjectRepository<T>
     */
    protected ObjectRepository $objectRepository;

    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityClass = $entityClass;
        /** @psalm-var  objectRepository<T> */
        $this->objectRepository = $entityManager->getRepository($this->entityClass);
    }

    /**
     * {@inheritDoc}
     */
    public function findAll(): array
    {
        return $this->objectRepository->findBy([]);
    }

    /**
     * @psalm-return ?T
     */
    public function find($id)
    {
        return $this->objectRepository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->objectRepository->findOneBy($criteria);
    }

    public function getClassName(): string
    {
        return $this->entityClass;
    }
}
