<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use RuntimeException;

class ParticipationServiceTest extends AbstractDatabaseTestCase
{
    use ProphecyTrait;

    private EntityManagerInterface $entityManager;
    private ParticipantRepository $participantRepo;
    private SlotRepository $slotRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(static::$container->get('security.user_password_encoder.generic')),
        ]);

        /** @var EntityManagerInterface $entityManager */
        $this->entityManager = $this->getDoctrine()->getManager();

        /** @var ParticipantRepository $participantRepo */
        $this->participantRepo = $this->entityManager->getRepository(Participant::class);
        $this->slotRepo = self::$container->get(SlotRepository::class);
    }

    /**
     * @test
     *
     * @testdox User can join a bookable, i.e. not locked, expired or fully booked meal.
     */
    public function joinSuccessSelfJoining(): void
    {
        // mock to fake a bookable meal and normal logged-in user
        $doorman = $this->getDoormanMock(true, false);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->participantRepo, $this->slotRepo);
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
     * @testdox A kitchen staff can add a user to a locked meal.
     */
    public function joinSuccessAssignedByKitchenStaff(): void
    {
        // mock to lock participation (no more joining) and fake logged-in kitchen staff
        $doorman = $this->getDoormanMock(false, true);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->participantRepo, $this->slotRepo);
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
     * @testdox User must not be able to join a non-bookable (locked, expired, or fully booked) meal.
     */
    public function joinFailure(): void
    {
        // mock to lock participation (no more joining) and fake normal logged-in user
        $doorman = $this->getDoormanMock(false, false);

        $profile = $this->getProfile('alice.meals');
        $meal = $this->getMeal();

        $sut = new ParticipationService($this->entityManager, $doorman, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($profile, $meal);

        $this->assertNull($out);
    }

    /**
     * @test
     *
     * @testdox User should be able to accept a locked, but not expired, meal offered by any participant.
     */
    public function acceptMealSuccess(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doormanMock = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, false, $offerer);

        $sut = new ParticipationService($this->entityManager, $doormanMock, $this->participantRepo, $this->slotRepo);
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
     * @test
     *
     * @testdox User must not be able to accept an expired meal.
     */
    public function acceptMealFailureMealExpired(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doormanMock = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, true, $offerer);

        $sut = new ParticipationService($this->entityManager, $doormanMock, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($user, $meal);

        $this->assertNull($out);
    }

    /**
     * @test
     *
     * @testdox User must not be able to accept a non-offered meal.
     */
    public function acceptMealFailureMealNotOffered(): void
    {
        // mock to lock participation (no more joining) and fake normal user login, i.e. no admin or kitchen staff
        $doormanMock = $this->getDoormanMock(false, false);

        $user = $this->getProfile('alice.meals');
        $meal = $this->getMeal(true);

        $sut = new ParticipationService($this->entityManager, $doormanMock, $this->participantRepo, $this->slotRepo);
        $out = $sut->join($user, $meal);

        $this->assertNull($out);
    }

    private function getMeal(bool $locked = false, bool $expired = false, ?Profile $offerer = null): Meal
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

    private function getProfile(string $username): Profile
    {
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $profile = $profileRepo->find($username);
        if (null === $profile) {
            throw new RuntimeException('profile not found: '.$username);
        }

        return $profile;
    }

    private function getDoormanMock(bool $userAllowedToJoin, bool $kitchenStaffLoggedIn): Doorman
    {
        $prophet = $this->prophesize(Doorman::class);
        $prophet->isUserAllowedToJoin(Argument::type(Meal::class))->willReturn($userAllowedToJoin);
        $prophet->isKitchenStaff()->willReturn($kitchenStaffLoggedIn);

        return $prophet->reveal();
    }
}
