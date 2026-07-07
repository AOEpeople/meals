<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;

trait ParticipantLoggingTrait
{
    /**
     * Log add action of staff member.
     *
     * Classes using this trait are expected to provide
     * `\Psr\Log\LoggerInterface $logger` and a `$doorman` service
     * with method `isKitchenStaff()`.
     */
    private function logAdd(Meal $meal, Participant $participant): void
    {
        if (false === is_object($this->doorman->isKitchenStaff())) {
            return;
        }

        $this->logger->info(
            'admin added {profile} to {meal} (Participant: {participantId})',
            [
                'participantId' => $participant->getId(),
                'profile' => $participant->getProfile(),
                'meal' => $meal,
            ]
        );
    }

    private function logRemove(Meal $meal, Participant $participant): void
    {
        if (true === $this->doorman->isKitchenStaff()) {
            $this->logger->info(
                'admin removed {profile} from {meal} (Meal: {mealId})',
                [
                    'profile' => $participant->getProfile(),
                    'meal' => $meal,
                    'mealId' => $meal->getId(),
                ]
            );
        }
    }
}
