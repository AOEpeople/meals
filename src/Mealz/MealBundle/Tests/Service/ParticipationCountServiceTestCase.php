<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ParticipationCountServiceTestCase extends AbstractParticipationServiceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(static::getContainer()->get('security.user_password_hasher')),
        ]);

        $doorman = $this->getDoormanMock(true, false);
        $dayRepo = self::getContainer()->get(DayRepository::class);

        $this->setParticipationService(
            new ParticipationService(
                $this->entityManager,
                $doorman,
                $dayRepo,
                $this->participantRepo,
                $this->slotRepo
            )
        );

        /* https://stackoverflow.com/questions/73209831/unitenum-cannot-be-cast-to-string */
        $price = self::$kernel->getContainer()->getParameter('mealz.meal.combined.price');
        $price = is_float($price) ? $price : 0;

        $dishRepo = static::getContainer()->get(DishRepository::class);
        $this->cms = new CombinedMealService($price, $this->entityManager, $dishRepo);
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
        $participation = (new ParticipationCountService())->getParticipationByDay($day);
        $this->assertEmpty($participation);
    }

    /**
     * @test
     *
     * @testdox Participation count for not locked and not expired meal is as expected
     */
    public function participationCountForNotLockedNotExpiredMeal(): void
    {
        $meals = $this->getMixedMealsOnSameDay();
        $weekDay = $this->createWeekForMealsOnSameDay($meals);
        $participation = (new ParticipationCountService())->getParticipationByDay($weekDay);
        $this->checkParticipationByDay($participation, $meals);
    }

    /**
     * @test
     *
     * @testdox Participation count for locked but not expired meal is as expected
     */
    public function participationCountForLockedNotExpiredMeal(): void
    {
        $meals = $this->getMixedMealsOnSameDay(true);
        $weekDay = $this->createWeekForMealsOnSameDay($meals);
        $participation = (new ParticipationCountService())->getParticipationByDay($weekDay);
        $this->checkParticipationByDay($participation, $meals);
    }

    /**
     * @test
     *
     * @testdox Participation count for locked but not expired meal is as expected
     */
    public function participationCountForLockedAndExpiredMeal(): void
    {
        $meals = $this->getMixedMealsOnSameDay(true, true);
        $weekDay = $this->createWeekForMealsOnSameDay($meals);
        $participation = (new ParticipationCountService())->getParticipationByDay($weekDay);
        $this->checkParticipationByDay($participation, $meals);
    }

    /**
     * @test
     *
     * @testdox Participation count for a day with combined meal without participants is as expected
     */
    public function participationCountForCombinedMealWithoutParticipants(): void
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
        ]);

        $combinedMeal = $this->getCombinedMeal($meals);
        $meals->add($combinedMeal);

        $participation = (new ParticipationCountService())->getParticipationByDay($combinedMeal->getDay());
        $this->checkParticipationByDay($participation, $meals);
    }

    /**
     * @test
     *
     * @testdox Participation count for a day with combined meal with participants is as expected
     */
    public function participationCountForCombinedMealWithParticipants(): void
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profiles = $profileRepo->findAll();
        $this->assertNotEmpty($profiles);

        $locked = false;
        $expired = false;

        $meals = new MealCollection([
            $this->getMeal($locked, $expired),
            $this->getMeal($locked, $expired, [$profiles[2], $profiles[1]]),
        ]);

        $bookedDishSlugs = [];
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $bookedDishSlugs[] = $meal->getDish()->getSlug();
        }

        $combinedMeal = $this->getCombinedMeal($meals, [$profiles[0]], $bookedDishSlugs);
        $meals->add($combinedMeal);

        $participation = (new ParticipationCountService())->getParticipationByDay($combinedMeal->getDay());
        $this->checkParticipationByDay($participation, $meals);
    }

    /**
     * @test
     *
     * @testdox Check if participation is possible (or not) for meals with limits (including combined meal)
     */
    public function checkParticipationIsPossibleForMealsWithLimits(): void
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profiles = $profileRepo->findAll();
        $this->assertNotEmpty($profiles);

        $locked = false;
        $expired = false;

        $mealA = $this->getMeal($locked, $expired);
        $mealA->setParticipationLimit(2);
        $mealB = $this->getMeal($locked, $expired, [$profiles[2], $profiles[1]]);
        $mealB->setParticipationLimit(5);

        $meals = new MealCollection([$mealA, $mealB]);

        $bookedDishSlugs = [];
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $bookedDishSlugs[] = $meal->getDish()->getSlug();
        }

        $combinedMeal = $this->getCombinedMeal($meals, [$profiles[0]], $bookedDishSlugs);
        $meals->add($combinedMeal);

        $participation = (new ParticipationCountService())->getParticipationByDay($combinedMeal->getDay());

        $totalParticipation = $participation[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];

        /*
         * Note:
         * We're interested in the total counts (connected dishes) not the single counts.
         * We have 0 participants for mealA and 2 participants for mealB plus 1 participant of the combined meal
         * That means we have 0.5 participations for dishA from mealA and 2.5 participations for dishB from mealB.
         * Or in a nutshell with limits: dishA: 0.5 of 2; dishB: 2.5 of 5;
         * The question is: Is it possible to join meal XXX with YYY more participant(s)?
         * Now, some test cases:
         */

        // Yes, dishA: 1.5 of 2
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealA->getDish()->getSlug()],
                1
            )
        );

        // No, dishA: 2.5 of 2
        $totalParticipation = $participation[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
        $this->assertFalse(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealA->getDish()->getSlug()],
                2
            )
        );

        // Yes, dishB: 3.5 of 5
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealB->getDish()->getSlug()],
                1
            )
        );

        // Yes, dishB: 4.5 of 5
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealB->getDish()->getSlug()],
                2
            )
        );

        // No, dishB: 5.5 of 5
        $this->assertFalse(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                [$mealB->getDish()->getSlug()],
                3
            )
        );

        // Yes, for combined dishes: dishA: 1 of 2; dishB: 3 of 5
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                $bookedDishSlugs,
                0.5
            )
        );

        // Yes, for two combined dishes: dishA: 1.5 of 2; dishB: 3.5 of 5
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                $bookedDishSlugs,
                1.0
            )
        );

        // Yes, for three combined dishes (or 1 full dish and one combined dish): dishA: 2 of 2; dishB: 4 of 5
        $this->assertTrue(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                $bookedDishSlugs,
                1.5
            )
        );

        // No, for four combined dishes (or 2 full dish or another combination): dishA: 2.5 of 2; dishB: 4.5 of 5
        $this->assertFalse(
            ParticipationCountService::isParticipationPossibleForDishes(
                $totalParticipation,
                $bookedDishSlugs,
                2.0
            )
        );
    }

    /**
     * @test
     *
     * @testdox Participation count for week days is empty for each day of a week if there aren't any meals
     */
    public function participationCountIsEmptyForEmptyWeek(): void
    {
        $currentWeek = $this->createWeek(new MealCollection());
        $participation = (new ParticipationCountService())->getParticipationByDays($currentWeek);
        $this->assertEmpty($participation);
    }

    /**
     * @test
     *
     * @testdox Participation count for week days is as expected for different meals.
     */
    public function participationCountForWeek(): void
    {
        $meals = $this->getMixedMeals();
        $this->getAndCheckParticipationByDays($meals);
    }

    /**
     * @test
     *
     * @testdox Participation is not possible if participation count array is empty
     */
    public function participationIsNotPossibleForEmptyParticipationsCount(): void
    {
        $meal = $this->getMeal();

        $this->assertFalse(
            ParticipationCountService::isParticipationPossibleForDishes(
                [],
                [$meal->getDish()->getSlug()],
                1
            )
        );

        $this->assertFalse(
            ParticipationCountService::isParticipationPossibleForDishes(
                [],
                [$meal->getDish()->getSlug()],
                1
            )
        );
    }

    /**
     * @test
     *
     * @testdox Participation is not possible if dish slugs array is empty
     */
    public function participationIsNotPossibleForEmptyDishSlugs(): void
    {
        $meals = $this->getMixedMeals();
        $participation = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];

            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $mealParticipation,
                    [],
                    1
                )
            );

            $totalParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $totalParticipation,
                    [],
                    1
                )
            );
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
        $participation = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];

            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $mealParticipation,
                    ['wrong-slug'],
                    1
                )
            );

            $totalParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $totalParticipation,
                    ['wrong-slug'],
                    1
                )
            );
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
        $participation = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $mealParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertTrue(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $mealParticipation,
                    [$mealDishSlug],
                    1
                )
            );

            $totalParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertTrue(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $totalParticipation,
                    [$mealDishSlug],
                    1
                )
            );
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
        $participation = $this->getAndCheckParticipationByDays($meals);

        foreach ($meals as $meal) {
            $date = $meal->getDay()->getDateTime()->format('Y-m-d');
            $mealDishSlug = $meal->getDish()->getSlug();

            $mealParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $mealParticipation,
                    [$mealDishSlug],
                    1
                )
            );

            $totalParticipation = $participation[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertFalse(
                ParticipationCountService::isParticipationPossibleForDishes(
                    $totalParticipation,
                    [$mealDishSlug],
                    1
                )
            );
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

        // Note: In production, we don't have more than 2 main dishes (or 2 variants per main dish), which means maximum 4 dishes to choose.
        // This should demonstrate that the participation count also works for a lot of meals on the same day.
        return new MealCollection([
            $this->getMeal($locked, $expired),
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

    /**
     * @return Meal[]
     *
     * @psalm-return array{0: Meal, 1: Meal, 2: Meal}
     */
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
        $this->assertGreaterThan(0, count($week->getDays()));

        $participations = (new ParticipationCountService())->getParticipationByDays($week);
        $this->assertNotEmpty($participations);

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
            $this->assertArrayHasKey($date, $participations);

            $this->assertArrayHasKey(ParticipationCountService::PARTICIPATION_COUNT_KEY, $participations[$date]);
            $this->assertArrayHasKey($meal->getId(), $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY]);
            $participation = $participations[$date][ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertArrayHasKey($mealDishSlug, $participation);
            $this->assertEquals($meal->getParticipants()->count(), $participation[$mealDishSlug]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $participation[$mealDishSlug]['limit']);

            $this->assertArrayHasKey(ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY, $participations[$date]);
            $totalParticipation = $participations[$date][ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];
            $this->assertArrayHasKey($mealDishSlug, $totalParticipation);
            $this->assertEquals($meal->getParticipants()->count(), $totalParticipation[$mealDishSlug]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $totalParticipation[$mealDishSlug]['limit']);
        }

        return $participations;
    }

    private function createWeekForMealsOnSameDay(MealCollection $meals): Day
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

        return $weekDay;
    }

    private function checkParticipationByDay(array $participations, MealCollection $meals): void
    {
        $this->assertArrayHasKey(ParticipationCountService::PARTICIPATION_COUNT_KEY, $participations);
        $this->assertArrayHasKey(ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY, $participations);

        $this->assertNotEmpty($participations[ParticipationCountService::PARTICIPATION_COUNT_KEY]);
        $this->assertNotEmpty($participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY]);

        $totalCounts = [];

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $this->assertArrayHasKey($meal->getId(), $participations[ParticipationCountService::PARTICIPATION_COUNT_KEY]);
            $mealDishSlug = $meal->getDish()->getSlug();
            $participation = $participations[ParticipationCountService::PARTICIPATION_COUNT_KEY][$meal->getId()];
            $this->assertArrayHasKey($mealDishSlug, $participation);
            $this->assertEquals($meal->getParticipants()->count(), $participation[$mealDishSlug]['count']);
            $this->assertEquals($meal->getParticipationLimit(), $participation[$mealDishSlug]['limit']);

            if ($meal->isCombinedMeal()) {
                /** @var Participant $participant */
                foreach ($meal->getParticipants() as $participant) {
                    /** @var Dish $dish */
                    foreach ($participant->getCombinedDishes() as $dish) {
                        if (!array_key_exists($dish->getSlug(), $totalCounts)) {
                            $totalCounts[$dish->getSlug()] = 0.0;
                        }

                        $totalCounts[$dish->getSlug()] += 0.5;
                    }
                }
            } else {
                if (!array_key_exists($mealDishSlug, $totalCounts)) {
                    $totalCounts[$mealDishSlug] = 0.0;
                }

                $totalCounts[$mealDishSlug] += $meal->getParticipants()->count();
            }
        }

        $totalParticipation = $participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY];

        foreach ($totalCounts as $dishSlug => $totalCount) {
            $this->assertArrayHasKey($dishSlug, $totalParticipation);
            $this->assertEquals($totalCount, $totalParticipation[$dishSlug]['count']);
        }
    }

    protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null
    ): void {
        echo 'not implemented.';
    }
}
