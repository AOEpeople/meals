<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\ReadableCollection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\LazyCriteriaCollection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository;
use Override;

/**
 * @template TKey as array-key
 * @template T of object
 *
 * @template-implements ObjectRepository<T>
 * @template-implements Selectable<TKey, T>
 */
abstract class BaseRepository implements ObjectRepository, Selectable
{
    private string $entityClass;

    private EntityManagerInterface $entityManager;

    /**
     * @psalm-var ObjectRepository<T>
     */
    protected ObjectRepository $objectRepository;

    public function __construct(EntityManagerInterface $entityManager, string $entityClass)
    {
        $this->entityClass = $entityClass;
        $this->entityManager = $entityManager;

        /** @psalm-var  ObjectRepository<T> */
        $this->objectRepository = $this->entityManager->getRepository($this->entityClass);
    }

    /**
     * Creates a new QueryBuilder instance that is pre-populated for this entity name.
     */
    public function createQueryBuilder(string $alias, ?string $indexBy = null): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select($alias)
            ->from($this->entityClass, $alias, $indexBy);
    }

    #[Override]
    public function findAll(): array
    {
        return $this->objectRepository->findBy([]);
    }

    #[Override]
    public function find($id)
    {
        return $this->objectRepository->find($id);
    }

    #[Override]
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    #[Override]
    public function findOneBy(array $criteria): ?object
    {
        return $this->objectRepository->findOneBy($criteria);
    }

    #[Override]
    public function getClassName(): string
    {
        return $this->entityClass;
    }

    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Select all elements from a selectable that match the expression and
     * return a new collection containing these elements.
     */
    #[Override]
    public function matching(Criteria $criteria)
    {
        $persister = $this->entityManager->getUnitOfWork()->getEntityPersister($this->entityClass);
        /** @psalm-var ReadableCollection<TKey, T>&Selectable<TKey, T> $collection */
        $collection = new LazyCriteriaCollection($persister, $criteria);

        return $collection;
    }
}
