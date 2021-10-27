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

    /**
     * Update state (enabled/disabled) of a given slot.
     */
    public function update(Slot $slot, array $data): void
    {
        $this->updateSlot($slot, $data);
        $this->em->persist($slot);
        $this->em->flush();
    }

    private function updateSlot(Slot $slot, array $data): void
    {
        if (isset($data['disabled'])) {
            $this->setDisabled($slot, $data['disabled']);
        }
    }

    private function setDisabled(Slot $slot, $state): void
    {
        if (!in_array($state, ['0', '1'], true)) {
            throw new InvalidArgumentException('invalid slot state');
        }
        $slot->setDisabled(('1' === $state));
    }
}
