<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\SlotRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

class SlotService
{
    private EntityManagerInterface $em;
    private ParticipationService $participantService;
    private SlotRepository $slotRepository;

    public function __construct(
        EntityManagerInterface $em,
        ParticipationService $participantService,
        SlotRepository $slotRepository
    )
    {
        $this->em = $em;
        $this->participantService = $participantService;
        $this->slotRepository = $slotRepository;
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

    public function delete(Slot $slot): void
    {
        $slot->setDeleted(true);

        $this->em->persist($slot);
        $this->em->flush();
    }

    public function getSlotStatusForDay(DateTime $datetime): array
    {
        $status = $this->participantService->getSlotsStatusOn($datetime);
        $slots = [];

        foreach ($status as $name => $count)
        {
            $slot = $this->slotRepository->findOneBy(["slug" => $name]);
            $slots[] = [
                "id" => $slot->getId(),
                "title" => $slot->getTitle(),
                "count" => $count,
                "limit" => $slot->getLimit(),
                "slug" => $slot->getSlug()
            ];
        }

        return $slots;
    }
}
