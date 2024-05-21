<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;

class ParticipationServiceTestCase extends AbstractParticipationServiceTestCase
{
    private DayRepository $dayRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(static::getContainer()->get('security.user_password_hasher')),
        ]);

        $doorman = $this->getDoormanMock(true, false);
        $this->dayRepo = self::getContainer()->get(DayRepository::class);

        $this->setParticipationService(
            new ParticipationService(
                $this->entityManager,
                $doorman,
                $this->dayRepo,
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
     * @testdox User can join a bookable, i.e. not locked, expired or fully booked meal.
     */
    public function testJoinSuccessSelfJoining(): void
    {
        // mock to fake a bookable meal and normal logged-in user
        $doorman = $this->getDoormanMock(true, false);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($profile->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());
        $this->assertNull($participant->getSlot());

        $this->assertArrayHasKey('offerer', $out);
        $this->assertNull($out['offerer']);
    }

    /**
     * @testdox A kitchen staff can add a user to a locked meal.
     */
    public function testJoinSuccessAssignedByKitchenStaff(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($profile->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());
        $this->assertNull($participant->getSlot());

        $this->assertArrayHasKey('offerer', $out);
        $this->assertNull($out['offerer']);
    }

    /**
     * @test
     *
     * @testdox Joining a meal without specifying a slot automatically assigns a slot.
     */
    public function joinSuccessAutoSelectSlot(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $this->createSlots([['title' => '12:00-12:30 Canteen']]);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($profile->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $slot = $participant->getSlot();
        $this->assertInstanceOf(Slot::class, $slot);
        $this->assertSame('12:00-12:30 Canteen', $slot->getTitle());

        $this->assertArrayHasKey('offerer', $out);
        $this->assertNull($out['offerer']);
    }

    /**
     * @test
     *
     * @testdox Joining a meal without specifying a slot automatically assigns the next available free slot.
     */
    public function joinSuccessAutoSelectFreeSlot(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $this->createSlots([
            'priority one slot' => ['title' => '12:00-12:30 Canteen', 'limit' => 1, 'order' => 1],
            'priority two slot; disabled' => ['title' => '12:30-13:00', 'order' => 2, 'disabled' => true],
            'priority three slot' => ['title' => '12:00-13:00 Take away', 'order' => 3],
        ]);

        $user1 = $this->getProfile('alice.meals');
        $user2 = $this->getProfile('bob.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);

        // occupy first slot
        $sut->join($user1, $meal);
        // join again to get the next slot
        $out = $sut->join($user2, $meal);

        $this->assertNotNull($out);
        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($user2->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $slot = $participant->getSlot();
        $this->assertInstanceOf(Slot::class, $slot);
        $this->assertSame('12:00-13:00 Take away', $slot->getTitle());
    }

    /**
     * @testdox Joining a meal without specifying a slot automatically assigns a slot.
     */
    public function testJoinSuccessAutoSelectSlot(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $this->createSlots([['title' => '12:00-12:30 Canteen']]);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($profile->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $slot = $participant->getSlot();
        $this->assertInstanceOf(Slot::class, $slot);
        $this->assertSame('12:00-12:30 Canteen', $slot->getTitle());

        $this->assertArrayHasKey('offerer', $out);
        $this->assertNull($out['offerer']);
    }

    /**
     * @testdox Joining a meal without specifying a slot automatically assigns the next available free slot.
     */
    public function testJoinSuccessAutoSelectFreeSlot(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $this->createSlots([
            'priority one slot' => ['title' => '12:00-12:30 Canteen', 'limit' => 1, 'order' => 1],
            'priority two slot; disabled' => ['title' => '12:30-13:00', 'order' => 2, 'disabled' => true],
            'priority three slot' => ['title' => '12:00-13:00 Take away', 'order' => 3],
        ]);

        $user1 = $this->getProfile('alice.meals');
        $user2 = $this->getProfile('bob.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);

        // occupy first slot
        $sut->join($user1, $meal);
        // join again to get the next slot
        $out = $sut->join($user2, $meal);

        $this->assertNotNull($out);
        $this->assertArrayHasKey('participant', $out);
        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($user2->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $slot = $participant->getSlot();
        $this->assertInstanceOf(Slot::class, $slot);
        $this->assertSame('12:00-13:00 Take away', $slot->getTitle());
    }

    /**
     * @testdox User must not be able to join a non-bookable (locked, expired, or fully booked) meal.
     */
    public function testJoinFailure(): void
    {
        // mock to lock participation (no more joining) and fake normal logged-in user
        $doorman = $this->getDoormanMock(false, false);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertNull($out);
    }

    /**
     * @testdox User should be able to accept a locked, but not expired, meal offered by any participant.
     */
    public function testAcceptMealSuccess(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doorman = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, false, [$offerer]);

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($user, $meal);

        $this->assertIsArray($out);
        $this->assertArrayHasKey('participant', $out);

        $participant = $out['participant'];
        $this->assertInstanceOf(Participant::class, $participant);
        $this->assertSame($user->getUsername(), $participant->getProfile()->getUsername());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());
        $this->assertNull($participant->getSlot());
    }

    /**
     * @testdox User must not be able to accept an expired meal.
     */
    public function testAcceptMealFailureMealExpired(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doorman = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, true, [$offerer]);

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($user, $meal);

        $this->assertNull($out);
    }

    /**
     * @testdox User must not be able to accept a non-offered meal.
     */
    public function testAcceptMealFailureMealNotOffered(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doorman = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $meal = $this->getMeal(true);

        $sut = new ParticipationService($this->entityManager, $doorman, $this->dayRepo, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($user, $meal);

        $this->assertNull($out);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can join a combined meal.
     */
    public function joinCombinedMealSuccess(): void
    {
        $profile = $this->getProfile('alice.meals');
        $this->checkJoinCombinedMealSuccess($profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with more than 2 slugs.
     */
    public function joinCombinedMealWithThreeMealsSuccess(): void
    {
        $profile = $this->getProfile('alice.meals');
        $this->checkJoinCombinedMealWithThreeMealsFail($profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with wrong slugs.
     */
    public function joinCombinedMealWithWrongSlugFail(): void
    {
        $profile = $this->getProfile('alice.meals');
        $this->checkJoinCombinedMealWithWrongSlugFail($profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with empty slugs.
     */
    public function joinCombinedMealWithEmptySlugFail(): void
    {
        $profile = $this->getProfile('alice.meals');
        $this->checkJoinCombinedMealWithEmptySlugFail($profile);
    }

    /**
     * @test
     *
     * @testdox
     */
    public function getBookedDishCombination(): void
    {
        $profile = $this->getProfile('alice.meals');

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

        $this->getParticipationService()->join($profile, $combinedMeal, $slot, $dishSlugs);

        $participant = $combinedMeal->getParticipant($profile);
        $dishCombination = $participant->getCombinedDishes();

        $this->assertNotEmpty($dishCombination);
        $this->assertSameSize($dishSlugs, $dishCombination);
        /** @var Dish $dish */
        foreach ($dishCombination as $dish) {
            $this->assertContains($dish->getSlug(), $dishSlugs);
        }
    }

    protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null
    ): void {
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $partMealSlot = $participant->getSlot();
        if (null !== $slot) {
            $this->assertNotNull($partMealSlot);
            $this->assertSame($slot->getSlug(), $partMealSlot->getSlug());
        } else {
            $this->assertNull($partMealSlot);
        }

        $partProfile = $participant->getProfile();
        $this->assertSame($profile->getFullName(), $partProfile->getFullName());
        $this->assertSame($profile->getCompany(), $partProfile->getCompany());

        if ($meal->isCombinedMeal()) {
            $this->assertNotEmpty($participant->getCombinedDishes());
            $this->assertCount(2, $participant->getCombinedDishes());
        }
    }

    private function createSlots(array $data): void
    {
        if (0 === count($data)) {
            return;
        }

        $entities = [];

        foreach ($data as $item) {
            $slot = new Slot();
            $slot->setTitle($item['title']);

            if (isset($item['limit'])) {
                $slot->setLimit($item['limit']);
            }
            if (isset($item['order'])) {
                $slot->setOrder($item['order']);
            }
            if (isset($item['disabled'])) {
                $slot->setDisabled($item['disabled']);
            }

            $entities[] = $slot;
        }

        $this->persistAndFlushAll($entities);
    }
}
