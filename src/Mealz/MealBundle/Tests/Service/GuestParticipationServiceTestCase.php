<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\AccountingBundle\Repository\PriceRepository;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadSlots;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Repository\GuestInvitationRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Override;

final class GuestParticipationServiceTestCase extends AbstractParticipationServiceTestCase
{
    private Profile $profile;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadSlots(),
        ]);

        /** @var ProfileRepositoryInterface $profileRepo */
        $profileRepo = self::getContainer()->get(ProfileRepositoryInterface::class);
        $roleRepo = self::getContainer()->get(RoleRepositoryInterface::class);
        $guestInvitationRepo = self::getContainer()->get(GuestInvitationRepositoryInterface::class);
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);

        $this->setParticipationService(
            new GuestParticipationService(
                $this->entityManager,
                $this->participantRepo,
                $profileRepo,
                $roleRepo,
                $this->slotRepo,
                $guestInvitationRepo,
                $mealRepo
            )
        );

        $dishRepo = static::getContainer()->get(DishRepository::class);
        $priceRepo = static::getContainer()->get(PriceRepository::class);
        $this->cms = new CombinedMealService($this->entityManager, $dishRepo, $priceRepo);

        /** @var Role $role */
        $role = $roleRepo->findOneBy(['sid' => Role::ROLE_GUEST]);

        $this->profile = new Profile();
        $this->profile->setRoles(new ArrayCollection([$role]));
        $this->profile->setFirstName('Max');
        $this->profile->setName('Mustermann');
        $this->profile->setCompany('Test Company');
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

        $this->getParticipationService()->join($profile, $meals);

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

        $this->getParticipationService()->join($profile, $meals, $slot);

        $participants = $this->participantRepo->findBy(['meal' => $meal]);
        $this->assertCount(1, $participants);

        $participant = $participants[0];
        $this->validateParticipant($participant, $profile, $meal, $slot);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can join a combined meal.
     */
    public function joinCombinedMealSuccess(): void
    {
        $this->checkJoinCombinedMealSuccess($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with more than 2 slugs.
     */
    public function joinCombinedMealWithThreeMealsSuccess(): void
    {
        $this->checkJoinCombinedMealWithThreeMealsFail($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with wrong slugs.
     */
    public function joinCombinedMealWithWrongSlugFail(): void
    {
        $this->checkJoinCombinedMealWithWrongSlugFail($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with empty slugs.
     */
    public function joinCombinedMealWithEmptySlugFail(): void
    {
        $this->checkJoinCombinedMealWithEmptySlugFail($this->profile);
    }

    #[Override]
    protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null
    ): void {
        $this->assertTrue($participant->isCostAbsorbed());
        $this->assertSame($meal->getId(), $participant->getMeal()->getId());

        $partMealSlot = $participant->getSlot();
        $this->assertNotNull($partMealSlot);
        if (null !== $slot) {
            $this->assertSame($slot->getSlug(), $partMealSlot->getSlug());
        }

        $partProfile = $participant->getProfile();
        $this->assertTrue($partProfile->isGuest());
        $this->assertSame($profile->getFullName(), $partProfile->getFullName());
        $this->assertSame($profile->getCompany(), $partProfile->getCompany());

        if ($meal->isCombinedMeal()) {
            $this->assertNotEmpty($participant->getCombinedDishes());
            $this->assertCount(2, $participant->getCombinedDishes());
        }
    }

    /**
     * @psalm-suppress InvalidNullableReturnType
     */
    #[Override]
    protected function getParticipationService(): GuestParticipationService|ParticipationService|null
    {
        if (parent::getParticipationService() instanceof GuestParticipationService) {
            return parent::getParticipationService();
        }

        return null;
    }
}
