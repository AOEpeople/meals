<?php

namespace Mealz\MealBundle\Twig\Extension;

use Doctrine\ORM\PersistentCollection;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;

class Participation extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'isParticipant' => new \Twig_Function_Method($this, 'isParticipant'),
        );
    }

    /**
     * @param Participant[] $userParticipations
     * @param PersistentCollection $meal
     */
    public function isParticipant($userParticipations, $mealParticipations)
    {
        foreach ($userParticipations as $participation) {
            if($mealParticipations->contains($participation)) {
                return true;
            }
        }

        return false;
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