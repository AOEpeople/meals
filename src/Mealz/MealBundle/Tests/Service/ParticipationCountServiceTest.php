<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

class ParticipationCountServiceTest extends AbstractParticipationServiceTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(static::$container->get('security.user_password_encoder.generic')),
        ]);
    }

    /**
     * @test
     *
     * @testdox Participation count is empty if there aren't any meals
     */
    public function participationCountIsEmptyForEmptyDay(): void
    {
        $day = new Day();
        $day->setDateTime(new DateTime());
        $participations = ParticipationCountService::getParticipationByDay($day);
        $this->assertEmpty($participations);
    }

    /**
     * @test
     *
     * @testdox Participation count for not locked and not expired meal is as expected
     */
    public function participationCountForNotLockedNotExpiredMeal(): void
    {
        $meals = $this->getMixedMealsOnSameDay(); // TODO check other cases locked and expired
        $participations = $this->getAndCheckParticipationDay($meals);

        foreach ($meals as $meal) {
            $participation = $participations[ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertEquals($meal->getParticipants()->count(), $participation[$meal->getDish()->getSlug()]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $participation[$meal->getDish()->getSlug()]['limit']);

            $totalParticipation = $participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertEquals($meal->getParticipants()->count(), $totalParticipation[$meal->getDish()->getSlug()]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $totalParticipation[$meal->getDish()->getSlug()]['limit']);
        }
    }

    /**
     * @test
     *
     * @testdox Participation count for week days is empty for each day of a week if there aren't any meals
     */
    public function participationCountIsEmptyForEmptyWeek(): void
    {
        $currentWeek = $this->createWeek(new MealCollection());
        $participations = ParticipationCountService::getParticipationByDays($currentWeek);
        $this->assertEmpty($participations);
    }

    /**
     * @test
     *
     * @testdox Participation count for week days is as expected for different meals.
     */
    public function participationCountForWeek(): void
    {
        $meals = $this->getMixedMeals();
        $participations = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertEquals($meal->getParticipants()->count(), $participation[$mealDishSlug]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $participation[$mealDishSlug]['limit']);

            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertEquals($meal->getParticipants()->count(), $totalParticipation[$mealDishSlug]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $totalParticipation[$mealDishSlug]['limit']);
        }
    }

    /**
     * @test
     *
     * @testdox Participation is not possible if participation count array is empty
     */
    public function participationIsNotPossibleForEmptyParticipationsCount(): void
    {
        $meal = $this->getMeal();

        $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
            [],
            [$meal->getDish()->getSlug()],
            1
        ));

        $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
            [],
            [$meal->getDish()->getSlug()],
            1
        ));
    }

    /**
     * TODO will be a test after #249690 is merged.
     *
     * @testdox Participation is not possible if dish slugs array is empty
     */
    public function participationIsNotPossibleForEmptyDishSlugs(): void
    {
        $meals = $this->getMixedMeals();
        $participations = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];

            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $participation,
                [],
                1
            ));

            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [],
                1
            ));
        }
    }

    /**
     * @test
     *
     * @testdox Participation is not possible if dish slugs array contains wrong dish slug
     */
    public function participationIsNotPossibleForWrongDishSlugs(): void
    {
        $meals = $this->getMixedMeals();
        $participations = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];

            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $participation,
                ['wrong-slug'],
                1
            ));

            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                ['wrong-slug'],
                1
            ));
        }
    }

    /**
     * @test
     *
     * @testdox Participation is possible for meals when there is no limit or where limit is not reached
     */
    public function participationIsPossibleForWeekWithLimitsWhereLimitIsNotReached(): void
    {
        $meals = $this->getMixedMeals(false);
        $participations = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertTrue(ParticipationCountService::isParticipationPossibleForDishes(
                $participation,
                [$mealDishSlug],
                1
            ));

            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertTrue(ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealDishSlug],
                1
            ));
        }
    }

    /**
     * @test
     *
     * @testdox Participation is not possible for meals where limit is reached
     */
    public function participationIsNotPossibleForWeekWhereLimitIsReached(): void
    {
        $meals = new MealCollection($this->getMixedMealsWithLimitReached());
        $participations = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $participation,
                [$mealDishSlug],
                1
            ));

            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealDishSlug],
                1
            ));
        }
    }

    private function getMixedMealsOnSameDay(bool $locked = false, bool $expired = false): MealCollection
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profiles = $profileRepo->findAll();
        $this->assertNotEmpty($profiles);

        $mealWithLimitA = $this->getMeal($locked, $expired, [$profiles[3], $profiles[1]]);
        $mealWithLimitA->setParticipationLimit($mealWithLimitA->getParticipants()->count() + 3);
        $mealWithLimitB = $this->getMeal($locked, $expired, [$profiles[2]]);
        $mealWithLimitB->setParticipationLimit($mealWithLimitB->getParticipants()->count() + 4);
        $mealWithLimitC = $this->getMeal($locked, $expired, [$profiles[3], $profiles[0], $profiles[2]]);
        $mealWithLimitC->setParticipationLimit($mealWithLimitC->getParticipants()->count() + 1);

        $mealLimitReachedA = $this->getMeal($locked, $expired, [$profiles[2], $profiles[1], $profiles[3]]);
        $mealLimitReachedA->setParticipationLimit($mealWithLimitA->getParticipants()->count());
        $mealLimitReachedB = $this->getMeal($locked, $expired, [$profiles[0]]);
        $mealLimitReachedB->setParticipationLimit($mealWithLimitB->getParticipants()->count());
        $mealLimitReachedC = $this->getMeal($locked, $expired, [$profiles[1], $profiles[count($profiles) - 1]]);
        $mealLimitReachedC->setParticipationLimit($mealWithLimitC->getParticipants()->count());

        return new MealCollection([
            $this->getMeal(),
            $this->getMeal($locked, $expired),
            $this->getMeal($locked, $expired),
            $this->getMeal($locked, $expired, [$profiles[0], $profiles[1]]),
            $this->getMeal($locked, $expired, [$profiles[2]]),
            $this->getMeal($locked, $expired, [$profiles[3], $profiles[0]]),
            $mealWithLimitA,
            $mealWithLimitB,
            $mealWithLimitC,
            $mealLimitReachedA,
            $mealLimitReachedB,
            $mealLimitReachedC,
        ]);
    }

    private function getMixedMeals(bool $withLimitIsReached = true): MealCollection
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profiles = $profileRepo->findAll();
        $this->assertNotEmpty($profiles);

        $mealWithLimitA = $this->getMeal(false, false, [$profiles[3], $profiles[1]]);
        $mealWithLimitA->setParticipationLimit($mealWithLimitA->getParticipants()->count() + 4);
        $mealWithLimitB = $this->getMeal(true, false, [$profiles[2]]);
        $mealWithLimitB->setParticipationLimit($mealWithLimitB->getParticipants()->count() + 3);
        $mealWithLimitC = $this->getMeal(true, true, [$profiles[3], $profiles[0], $profiles[2]]);
        $mealWithLimitC->setParticipationLimit($mealWithLimitC->getParticipants()->count() + 10);

        $meals = [
            $this->getMeal(),
            $this->getMeal(true),
            $this->getMeal(true, true),
            $this->getMeal(false, false, [$profiles[0], $profiles[1]]),
            $this->getMeal(true, false, [$profiles[2]]),
            $this->getMeal(true, true, [$profiles[3], $profiles[0]]),
            $mealWithLimitA,
            $mealWithLimitB,
            $mealWithLimitC,
        ];

        if ($withLimitIsReached) {
            $meals = array_merge($this->getMixedMealsWithLimitReached(), $meals);
        }

        return new MealCollection($meals);
    }

    private function getMixedMealsWithLimitReached(): array
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profiles = $profileRepo->findAll();
        $this->assertNotEmpty($profiles);

        $mealLimitReachedA = $this->getMeal(false, false, [$profiles[2], $profiles[1], $profiles[3]]);
        $mealLimitReachedA->setParticipationLimit($mealLimitReachedA->getParticipants()->count());
        $mealLimitReachedB = $this->getMeal(true, false, [$profiles[0]]);
        $mealLimitReachedB->setParticipationLimit($mealLimitReachedB->getParticipants()->count());
        $mealLimitReachedC = $this->getMeal(true, true, [$profiles[1], $profiles[count($profiles) - 1]]);
        $mealLimitReachedC->setParticipationLimit($mealLimitReachedC->getParticipants()->count());

        return [$mealLimitReachedA, $mealLimitReachedB, $mealLimitReachedC];
    }

    private function getAndCheckParticipationByDays(MealCollection $meals): array
    {
        $week = $this->createWeek($meals);
        $participations = ParticipationCountService::getParticipationByDays($week);

        /** @var Day $day */
        foreach ($week->getDays() as $day) {
            $date = $day->getDateTime()->format('Y-m-d');
            if (isset($participations[$date])) {
                $this->assertGreaterThan(0, count($day->getMeals()));
                $this->assertNotEmpty($participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY]);
                $this->assertNotEmpty($participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY]);
            } else {
                $this->assertCount(0, $day->getMeals());
            }
        }

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $this->assertArrayHasKey($meal->getId(), $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY]);
            $this->assertArrayHasKey($mealDishSlug, $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()]);
            $this->assertArrayHasKey($mealDishSlug, $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY]);
        }

        return $participations;
    }

    private function getAndCheckParticipationDay(MealCollection $meals): array
    {
        $date = null;
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            if (null === $date) {
                $date = $meal->getDateTime()->format('Y-m-d');
                continue;
            }

            // Check if meals are on the same day
            $this->assertEquals($date, $meal->getDateTime()->format('Y-m-d'));
        }

        // Note: we need a week otherwise participation count is empty
        $week = $this->createWeek($meals);

        $weekDay = $week->getDays()->filter(fn (Day $day) => $day->getDateTime()->format('Y-m-d') === $date)->first();
        $this->assertNotNull($weekDay);

        $participations = ParticipationCountService::getParticipationByDay($weekDay);

        $this->assertNotEmpty($participations[ParticipationCountService::PARTICIPATION_COUNT_KEY]);
        $this->assertNotEmpty($participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY]);

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $countByMeal = $participations[ParticipationCountService::PARTICIPATION_COUNT_KEY];
            $this->assertArrayHasKey($meal->getId(), $countByMeal);
            $count = $countByMeal[$meal->getId()];
            $this->assertArrayHasKey($meal->getDish()->getSlug(), $count);

            $totalCount = $participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertArrayHasKey($meal->getDish()->getSlug(), $totalCount);
        }

        return $participations;
    }
}
