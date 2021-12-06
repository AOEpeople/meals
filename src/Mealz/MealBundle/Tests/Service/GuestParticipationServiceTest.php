<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadSlots;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;

class GuestParticipationServiceTest extends AbstractParticipationServiceTest
{
    private ParticipantRepository $participantRepo;
    private SlotRepository $slotRepo;

    private GuestParticipationService $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadSlots(),
        ]);

        $this->participantRepo = $this->entityManager->getRepository(Participant::class);
        $profileRepo = $this->entityManager->getRepository(Profile::class);
        $roleRepo = $this->entityManager->getRepository(Role::class);
        $this->slotRepo = self::$container->get(SlotRepository::class);

        $this->sut = new GuestParticipationService(
            $this->entityManager,
            $this->participantRepo,
            $profileRepo,
            $roleRepo,
            $this->slotRepo
        );
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can join a meal without specifying a time slot.
     */
    public function joinSuccessWithoutSlot(): void
    {
        $profile = new Profile();
        $profile->setFirstName('Max');
        $profile->setName('Mustermann');
        $profile->setCompany('Test Company');

        $meal = $this->getMeal();
        $meals = new MealCollection([$meal]);

        $this->sut->join($profile, $meals, null);

        $participants = $this->participantRepo->findBy(['meal' => $meal]);
        $this->assertCount(1, $participants);

        $participant = $participants[0];
        $this->assertInstanceOf(Participant::class, $participant);
        $slot = $this->slotRepo->findOneBy(['slug' => 'active-wo-limit']);
        $this->assertInstanceOf(Slot::class, $slot);
        $this->validateParticipant($participant, $profile, $meal, $slot);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can join a meal with a specific time slot.
     */
    public function joinSuccessWithSlot(): void
    {
        $profile = new Profile();
        $profile->setFirstName('Max');
        $profile->setName('Mustermann');
        $profile->setCompany('Test Company');

        $meal = $this->getMeal();
        $meals = new MealCollection([$meal]);

        $slot = $this->slotRepo->findOneBy(['slug' => 'active-w-limit']);
        $this->assertInstanceOf(Slot::class, $slot);

        $this->sut->join($profile, $meals, $slot);

        $participants = $this->participantRepo->findBy(['meal' => $meal]);
        $this->assertCount(1, $participants);

        $participant = $participants[0];
        $this->validateParticipant($participant, $profile, $meal, $slot);
    }

    private function validateParticipant(Participant $participant, Profile $profile, Meal $meal, Slot $slot): void
    {
        $this->assertTrue($participant->isCostAbsorbed());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $partMealSlot = $participant->getSlot();
        $this->assertNotNull($partMealSlot);
        $this->assertSame($slot->getSlug(), $partMealSlot->getSlug());

        $partProfile = $participant->getProfile();
        $this->assertTrue($partProfile->isGuest());
        $this->assertSame($profile->getFullName(), $partProfile->getFullName());
        $this->assertSame($profile->getCompany(), $partProfile->getCompany());
    }
}
