<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Slot;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class SlotService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateState(Slot $slot, string $state): void
    {
        if (!in_array($state, ['0', '1'], true)) {
            throw new InvalidArgumentException('invalid slot state');
        }

        $slot->setDisabled('1' === $state);

        $this->em->persist($slot);
        $this->em->flush();
    }
}
