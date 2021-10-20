<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class SlotRepository
{
    private  ObjectRepository $objectRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->objectRepository = $entityManager->getRepository(Slot::class);
    }

    /**
     * Find and returns all the available slots.
     *
     * @param bool $enabled Flag to return only active slots.
     */
    public function findAll(bool $enabled = false): SlotCollection
    {
        if ($enabled) {
            $slots = $this->objectRepository->findBy(['disabled' => 0]);
        } else {
            $slots = $this->objectRepository->findAll();
        }

        return new SlotCollection($slots);
    }
}
