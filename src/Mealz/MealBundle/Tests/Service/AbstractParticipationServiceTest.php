<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

abstract class AbstractParticipationServiceTest extends AbstractDatabaseTestCase
{
    protected EntityManagerInterface $entityManager;
    protected CombinedMealService $cms;
    /** @var ParticipationService|GuestParticipationService */
    private $sut;
    protected ParticipantRepository $participantRepo;
    protected SlotRepository $slotRepo;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /* @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->getDoctrine()->getManager();
    }

    protected function checkJoinMealWithDishSlugsSuccess(Profile $profile)
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
        ]);
        $slot = null;
        $dishSlugs = null;
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $dishSlugs[] = $meal->getDish()->getSlug();
        }

        $this->sut->join($profile, $profile->isGuest() ? new MealCollection([$meals[0]]) : $meals[0], $slot, $dishSlugs);

        $participants = $this->participantRepo->findBy(['meal' => $meals[0]]);
        $this->assertCount(1, $participants);

        $participant = $participants[0];
        $this->validateParticipant($participant, $profile, $meals[0], $slot);
        $this->assertEmpty($participant->getCombinedDishes());
    }

    protected function checkJoinCombinedMealSuccess(Profile $profile)
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
        ]);

        $combinedMeal = $this->getCombinedMeal($meals);

        $slot = null;
        $dishSlugs = null;
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $dishSlugs[] = $meal->getDish()->getSlug();
        }

        $this->sut->join($profile, $profile->isGuest() ? new MealCollection([$combinedMeal]) : $combinedMeal, $slot, $dishSlugs);

        $participants = $this->participantRepo->findBy(['meal' => $combinedMeal]);
        $this->assertCount(1, $participants);

        $participant = $participants[0];
        $this->validateParticipant($participant, $profile, $combinedMeal, $slot);
    }

    protected function checkJoinCombinedMealWithThreeMealsSuccess(Profile $profile)
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
            $this->getMeal(),
        ]);

        $combinedMeal = $this->getCombinedMeal($meals);

        $slot = null;
        $dishSlugs = null;
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $dishSlugs[] = $meal->getDish()->getSlug();
        }

        $this->expectException(ParticipationException::class);
        $this->sut->join($profile, $profile->isGuest() ? new MealCollection([$combinedMeal]) : $combinedMeal, $slot, $dishSlugs);
    }

    protected function checkJoinCombinedMealWithWrongSlugFail(Profile $profile)
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
        ]);

        $combinedMeal = $this->getCombinedMeal($meals);

        $slot = null;
        $dishSlugs = [$meals[0]->getDish()->getSlug(), 'wrong-slug'];

        $this->expectException(ParticipationException::class);
        $this->sut->join($profile, $profile->isGuest() ? new MealCollection([$combinedMeal]) : $combinedMeal, $slot, $dishSlugs);
    }

    protected function checkJoinCombinedMealWithEmptySlugFail(Profile $profile)
    {
        $meals = new MealCollection([
            $this->getMeal(),
            $this->getMeal(),
        ]);

        $combinedMeal = $this->getCombinedMeal($meals);

        $slot = null;
        $dishSlugs = [];

        $this->expectException(ParticipationException::class);
        $this->sut->join($profile, $profile->isGuest() ? new MealCollection([$combinedMeal]) : $combinedMeal, $slot, $dishSlugs);
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

        $week = new Week();
        $week->setYear(intval($dateTime->format('o')));
        $week->setCalendarWeek(intval($dateTime->format('W')));
        $days = $week->getDays();

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $date = $meal->getDateTime()->format('Y-m-d');
            if (!isset($mealsByDay[$date])) {
                $day = new Day();
                $day->setDateTime(clone $meal->getDateTime());
                $day->setLockParticipationDateTime(clone $meal->getLockDateTime());
                $day->setWeek($week);

                $day->addMeal($meal);
                $days->add($day);
                $mealsByDay[$date] = $day;
            } else {
                $day = $mealsByDay[$date];
                $day->addMeal($meal);
            }
        }

        $this->entityManager->persist($week);
        $this->entityManager->flush();

        return $week;
    }

    protected function getCombinedMeal(MealCollection $meals): Meal
    {
        $week = $this->createWeek($meals);
        $this->assertNotEmpty($week->getDays());

        return $this->createOrGetCombinedMeal($meals[0]->getDay());
    }

    protected function createOrGetCombinedMeal(Day $day): Meal
    {
        /** @var Meal $meal */
        foreach ($day->getMeals() as $meal) {
            if ($meal->getDish()->isCombinedDish()) {
                return $meal;
            }
        }

        // Creates combined meal(s)
        $this->cms->update($day->getWeek());

        /** @var MealRepository $mealRepo */
        $mealRepo = $this->getDoctrine()->getRepository(Meal::class);
        $combinedMeal = $mealRepo->findOneByDateAndDish($day->getDateTime()->format('Y-m-d'), Dish::COMBINED_DISH_SLUG);
        $this->assertNotNull($combinedMeal);

        return $combinedMeal;
    }

    protected function getParticipationService()
    {
        return $this->sut;
    }

    /**
     * @param ParticipationService|GuestParticipationService $service
     */
    protected function setParticipationService($service)
    {
        $this->sut = $service;
    }

    abstract protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null);
}
