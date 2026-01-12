<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Repository;

use App\Mealz\AccountingBundle\Entity\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 */
class PriceRepository extends ServiceEntityRepository implements PriceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    /**
     * @return Collection<int, Price>
     */
    public function findAll(): Collection
    {
        return new ArrayCollection(parent::findAll());
    }

    public function findByYear(int $year): ?Price
    {
        return $this->findOneBy(['year' => $year]);
    }
}
