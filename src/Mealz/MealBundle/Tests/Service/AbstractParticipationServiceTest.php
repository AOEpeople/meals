<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\WeekService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

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

    protected function getMeal(bool $locked = false, bool $expired = false, array $profiles = [], bool $offering = true, ?Dish $dish = null): Meal
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

        $meal = $this->createMeal($dish, $mealDate);
        $meal->setDay($day);
        $meal->getDay()->addMeal($meal);

        $entities = [$meal->getDish(), $day, $meal];

        foreach ($profiles as $profile) {
            $participant = new Participant($profile, $meal);
            if ($offering) {
                $participant->setOfferedAt(time());
            }
            $entities[] = $participant;
        }

        $this->persistAndFlushAll($entities);
        $this->entityManager->refresh($meal);

        return $meal;
    }

    protected function getProfile(string $username): Profile
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profile = $profileRepo->find($username);
        if (null === $profile) {
            throw new RuntimeException('profile not found: ' . $username);
        }

        return $profile;
    }

    protected function createWeek(MealCollection $meals): Week
    {
        // Note: We need one datetime of a meal (or we take the current timestamp) to generate a week
        $dateTime = count($meals) > 0 ? $meals[0]->getDateTime() : new DateTime();

        $dateTimeModifier = (string) self::$kernel->getContainer()->getParameter('mealz.lock_toggle_participation_at');
        $week = WeekService::generateEmptyWeek($dateTime, $dateTimeModifier);

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            /** @var Day $weekDay */
            $weekDay = $week->getDays()->filter(fn (Day $day) => $day->getDateTime()->format('Y-m-d') === $meal->getDateTime()->format('Y-m-d'))->first();
            $weekDay->addMeal($meal);
        }

        $this->entityManager->persist($week);
        $this->entityManager->flush();

        return $week;
    }
}
