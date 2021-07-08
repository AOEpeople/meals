<?php

namespace Mealz\MealBundle\Twig\Extension;

use Doctrine\ORM\PersistentCollection;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Twig\TwigFunction;
use Twig_Extension;

class Participation extends Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new TwigFunction('isParticipant', [$this, 'isParticipant']),
        );
    }

    /**
     * @param Participant[] $userParticipations
     * @param PersistentCollection $meal
     */
    public function isParticipant($userParticipations, $mealParticipations)
    {
        foreach ($userParticipations as $participation) {
            if ($mealParticipations->contains($participation)) {
                return $participation;
            }
        }

        return null;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'participation';
    }
}
