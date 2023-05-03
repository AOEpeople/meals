<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Helper;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;

class ParticipationHelper
{
    /**
     * helper function to sort participants by their name or guest name.
     *
     * @param mixed $participantRepo
     * @param mixed $participants
     *
     * @return mixed
     */
    public function sortParticipantsByName($participantRepo, $participants)
    {
        usort($participants, [$participantRepo, 'compareNameOfParticipants']);

        return $participants;
    }

    /**
     * @param Participant[] $participants
     *
     * @psalm-return array<string, array<string, array{booked: non-empty-list<int>}>>
     */
    public function groupBySlotAndProfileID(array $participants): array
    {
        $groupedParticipants = [];

        foreach ($participants as $participant) {
            $slot = $participant->getSlot();

            if (null !== $slot && array_key_exists($slot->getTitle(), $groupedParticipants) && array_key_exists($participant->getProfile()->getFullName(), $groupedParticipants[$slot->getTitle()])) {
                continue;
            }

            $groupedParticipants = array_merge_recursive($groupedParticipants, $this->getParticipationbySlot($participant, $slot));
        }

        return $groupedParticipants;
    }

    protected function compareNameOfParticipants(Participant $participant1, Participant $participant2): int
    {
        $result = strcasecmp($participant1->getProfile()->getName(), $participant2->getProfile()->getName());

        if (0 !== $result) {
            return $result;
        } elseif ($participant1->getMeal()->getDateTime() < $participant2->getMeal()->getDateTime()) {
            return 1;
        } elseif ($participant1->getMeal()->getDateTime() > $participant2->getMeal()->getDateTime()) {
            return -1;
        }

        return 0;
    }

    private function getParticipationbySlot(Participant $participant, ?Slot $slot): array
    {
        $slots = [];

        /** @var Meal $meal */
        foreach ($participant->getMeal()->getDay()->getMeals() as $meal) {
            if (null !== $meal->getParticipant($participant->getProfile()) && (null === $slot || $slot->isDisabled() || $slot->isDeleted())) {
                $slots[''][$participant->getProfile()->getFullName()]['booked'][] = $meal->getDish()->getId();
                continue;
            }

            if (null !== $meal->getParticipant($participant->getProfile())) {
                $slots[$slot->getTitle()][$participant->getProfile()->getFullName()]['booked'][] = $meal->getDish()->getId();
            }
        }

        return $slots;
    }
}
