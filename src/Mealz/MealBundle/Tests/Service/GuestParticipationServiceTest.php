<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadSlots;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;

class GuestParticipationServiceTest extends AbstractParticipationServiceTest
{
    private Profile $profile;

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

        $this->setParticipationService(new GuestParticipationService(
            $this->entityManager,
            $this->participantRepo,
            $profileRepo,
            $roleRepo,
            $this->slotRepo
        ));

        $price = (float) self::$kernel->getContainer()->getParameter('mealz.meal.combined.price');
        $dishRepo = static::$container->get(DishRepository::class);
        $this->cms = new CombinedMealService($price, $this->entityManager, $dishRepo);

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
    public function joinCombinedMealSuccess()
    {
        $this->checkJoinCombinedMealSuccess($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with more than 2 slugs.
     */
    public function joinCombinedMealWithThreeMealsSuccess()
    {
        $this->checkJoinCombinedMealWithThreeMealsSuccess($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with wrong slugs.
     */
    public function joinCombinedMealWithWrongSlugFail()
    {
        $this->checkJoinCombinedMealWithWrongSlugFail($this->profile);
    }

    /**
     * @test
     *
     * @testdox An anonymous user (Profile) can't join a combined meal with empty slugs.
     */
    public function joinCombinedMealWithEmptySlugFail()
    {
        $this->checkJoinCombinedMealWithEmptySlugFail($this->profile);
    }

    protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null): void
    {
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

    protected function getParticipationService(): ?GuestParticipationService
    {
        if (parent::getParticipationService() instanceof GuestParticipationService) {
            return parent::getParticipationService();
        }

        return null;
    }
}
