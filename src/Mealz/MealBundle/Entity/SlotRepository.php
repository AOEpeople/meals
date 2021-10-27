<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SlotRepository implements ObjectRepository
{
    /**
     * @psalm-var  ObjectRepository<Slot>
     */
    private ObjectRepository $objectRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->objectRepository = $entityManager->getRepository(Slot::class);
    }

    /**
     * @return Slot[]
     */
    public function findAll(): array
    {
        return $this->objectRepository->findAll();
    }

    public function find($id): ?Slot
    {
        return $this->objectRepository->find($id);
    }

    /**
     * @return Slot[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->objectRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function findOneBy(array $criteria): ?Slot
    {
        return $this->objectRepository->findOneBy($criteria);
    }

    /**
     * @inheritDoc
     */
    public function getClassName(): string
    {
        return Slot::class;
    }
}
