<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractParticipationServiceTest extends AbstractDatabaseTestCase
{
    protected EntityManagerInterface $entityManager;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    protected function getMeal(bool $locked = false, bool $expired = false, ?Profile $offerer = null): Meal
    {
        if ($expired) {
            $mealDate = new DateTime('-1 hour');
            $mealLockDate = new DateTime('-12 hours');
        } elseif ($locked) {
            $mealDate = new DateTime('+4 hour');
            $mealLockDate = new DateTime('-8 hours');
        } else {
            $mealDate = new DateTime('+16 hour');
            $mealLockDate = new DateTime('+4 hours');
        }

        $day = new Day();
        $day->setLockParticipationDateTime($mealLockDate);
        $day->setDateTime($mealDate);

        $meal = $this->createMeal(null, $mealDate);
        $meal->setDay($day);

        $entities = [$meal->getDish(), $day, $meal];

        if ($offerer) {
            $participant = new Participant($offerer, $meal);
            $participant->setOfferedAt(time());
            $entities[] = $participant;
        }

        $this->persistAndFlushAll($entities);
        $this->entityManager->refresh($meal);

        return $meal;
    }
}
