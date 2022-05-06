<?php

namespace App\Mealz\MealBundle\Twig\Extension;

use App\Mealz\MealBundle\Entity\Participant;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\PersistentCollection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Participation extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isParticipant', [$this, 'isParticipant']),
        ];
    }

    /**
     * @param Participant[]        $userParticipations
     * @param PersistentCollection $meal
     */
    public function isParticipant(array $userParticipations, Collection $mealParticipations)
    {
        foreach ($userParticipations as $participation) {
            if ($mealParticipations->contains($participation)) {
                return $participation;
            }
        }

        return null;
    }
}
