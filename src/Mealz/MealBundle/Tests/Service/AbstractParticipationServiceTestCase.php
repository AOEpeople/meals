<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

abstract class AbstractParticipationServiceTestCase extends AbstractDatabaseTestCase
{
    use ProphecyTrait;

    protected EntityManagerInterface $entityManager;
    protected CombinedMealService $cms;
    protected OfferService $offerService;
    protected ParticipantRepositoryInterface $participantRepo;
    protected SlotRepository $slotRepo;
    /** @var ParticipationService|GuestParticipationService */
    private $sut;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        /* @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->getDoctrine()->getManager();

        $this->participantRepo = self::getContainer()->get(ParticipantRepositoryInterface::class);
        $this->slotRepo = self::getContainer()->get(SlotRepository::class);
    }

    protected function checkJoinMealWithDishSlugsSuccess(Profile $profile): void
    {
        $meals = [
            $this->getMeal(),
            $this->getMeal(),
        ];
        $slot = null;
        $dishSlugs = null;

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

    protected function checkJoinCombinedMealSuccess(Profile $profile): void
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

    protected function checkJoinCombinedMealWithThreeMealsFail(Profile $profile): void
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

    protected function checkJoinCombinedMealWithWrongSlugFail(Profile $profile): void
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

    protected function checkJoinCombinedMealWithEmptySlugFail(Profile $profile): void
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

    protected function getMeal(
        bool $locked = false, bool $expired = false, array $profiles = [], bool $offering = true, ?Dish $dish = null
    ): Meal {
        $zeroMinAndSec = static fn (DateTime $date): DateTime => $date->setTime((int) $date->format('H'), 0);

        if ($expired) {
            $mealDate = $zeroMinAndSec(new DateTime('-1 hour'));
            $mealLockDate = $zeroMinAndSec(new DateTime('-12 hours'));
        } elseif ($locked) {
            $mealDate = $zeroMinAndSec(new DateTime('+4 hour'));
            $mealLockDate = $zeroMinAndSec(new DateTime('-8 hours'));
        } else {
            $mealDate = $zeroMinAndSec(new DateTime('+16 hour'));
            $mealLockDate = $zeroMinAndSec(new DateTime('+4 hours'));
        }

        $day = new Day();
        $day->setLockParticipationDateTime($mealLockDate);
        $day->setDateTime($mealDate);

        $meal = $this->createMeal($dish, $day);
        $meal->getDay()->addMeal($meal);

        $entities = [$meal];

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

    protected function getCombinedMeal(MealCollection $meals, array $profiles = [], array $dishSlugs = []): Meal
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

        $week = $this->createWeek($meals);
        $this->assertNotEmpty($week->getDays());

        return $this->createCombinedMeal($meals[0]->getDay(), $profiles, $dishSlugs);
    }

    private function createCombinedMeal(Day $day, array $profiles = [], array $dishSlugs = []): Meal
    {
        $dishes = [];
        if (!empty($profiles)) {
            $this->assertNotEmpty($dishSlugs);

            $flippedDishSlugs = array_flip($dishSlugs);
            /** @var Meal $meal */
            foreach ($day->getMeals() as $meal) {
                if (isset($flippedDishSlugs[$meal->getDish()->getSlug()])) {
                    $dishes[] = $meal->getDish();
                }
            }

            $this->assertSameSize($flippedDishSlugs, $dishes);
        }

        // Creates combined meal(s)
        $this->cms->update($day->getWeek());

        /** @var MealRepositoryInterface $mealRepo */
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);
        $combinedMeal = $mealRepo->findOneByDateAndDish($day->getDateTime(), Dish::COMBINED_DISH_SLUG);
        $this->assertNotNull($combinedMeal);

        $participants = [];
        foreach ($profiles as $profile) {
            $participant = new Participant($profile, $combinedMeal);
            $participant->setCombinedDishes($dishes);
            $this->assertGreaterThan(0, $participant->getCombinedDishes()->count());
            $participants[] = $participant;
        }

        if (!empty($participants)) {
            $this->persistAndFlushAll($participants);
            $this->entityManager->refresh($combinedMeal);
            $this->assertGreaterThan(0, $combinedMeal->getParticipants()->count());
        }

        $this->assertSameSize($profiles, $combinedMeal->getParticipants());

        return $combinedMeal;
    }

    /**
     * @return GuestParticipationService|ParticipationService
     */
    protected function getParticipationService()
    {
        return $this->sut;
    }

    protected function setParticipationService(ParticipationService|GuestParticipationService $service): void
    {
        $this->sut = $service;
    }

    protected function getDoormanMock(bool $userAllowedToJoin, bool $kitchenStaffLoggedIn): Doorman
    {
        $prophet = $this->prophesize(Doorman::class);
        $prophet->isUserAllowedToJoin(Argument::type(Meal::class), Argument::type('array'))->willReturn($userAllowedToJoin);
        $prophet->isKitchenStaff()->willReturn($kitchenStaffLoggedIn);

        return $prophet->reveal();
    }

    abstract protected function validateParticipant(
        Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null
    );
}
