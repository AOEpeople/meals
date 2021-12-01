<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\ProfileRepository;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Entity\RoleRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

class GuestParticipationService
{
    use ParticipationServiceTrait;

    private EntityManagerInterface $entityManager;
    private ParticipantRepository $participantRepo;
    private ProfileRepository $profileRepo;
    private RoleRepository $roleRepo;
    private SlotRepository $slotRepo;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepo,
        ProfileRepository $profileRepo,
        RoleRepository $roleRepo,
        SlotRepository $slotRepo
    ) {
        $this->entityManager = $entityManager;
        $this->participantRepo = $participantRepo;
        $this->profileRepo = $profileRepo;
        $this->roleRepo = $roleRepo;
        $this->slotRepo = $slotRepo;
    }

    /**
     * @throws ParticipationException
     */
    public function join(Profile $profile, Collection $meals, ?Slot $slot): void
    {
        $mealDate = $meals->first()->getDateTime();

        $guestProfile = $this->getCreateGuestProfile(
            $profile->getFirstName(),
            $profile->getName(),
            $profile->getCompany(),
            $mealDate
        );

        if (null === $slot || !$this->slotIsAvailable($slot, $mealDate)) {
            $slot = $this->getNextFreeSlot($mealDate);
        }

        $this->register($guestProfile, $meals, $slot);
    }

    /**
     * Registers user with $profile to given meals and slot.
     *
     * @param Collection<int, Meal> $meals
     *
     * @throws ParticipationException
     * @throws Exception
     */
    private function register(Profile $profile, Collection $meals, ?Slot $slot): void
    {
        $this->validateBookableMeals($meals);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($profile);
            $this->create($profile, $meals, $slot);

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (Exception $exc) {
            $this->entityManager->rollBack();
            throw $exc;
        }
    }

    /**
     * @param Collection<int, Meal> $meals
     *
     * @throws ParticipationException
     */
    private function validateBookableMeals(Collection $meals): void
    {
        foreach ($meals as $meal) {
            if (!$this->mealIsBookable($meal)) {
                throw new ParticipationException(
                    'meal not bookable',
                    ParticipationException::ERR_MEAL_NOT_BOOKABLE, null,
                    ['meal' => $meal]
                );
            }
        }
    }

    /**
     * Create guest participation.
     *
     * @param Collection<int, Meal> $meals
     */
    private function create(Profile $profile, Collection $meals, ?Slot $slot): void
    {
        foreach ($meals as $meal) {
            $participation = new Participant($profile, $meal);
            $participation->setCostAbsorbed(true);
            if (null !== $slot) {
                $participation->setSlot($slot);
            }

            $this->entityManager->persist($participation);
        }
    }

    private function getCreateGuestProfile(
        string $firstName,
        string $lastName,
        string $company,
        DateTime $mealDate
    ): Profile {
        $guestProfileID = sprintf('%s.%s_%s', $firstName, $lastName, $mealDate->format('Y-m-d'));
        $guestProfile = $this->profileRepo->find($guestProfileID);
        if (($guestProfile instanceof Profile) && $guestProfile->isGuest()) {
            return $guestProfile;
        }

        $profile = new Profile();
        $profile->setUsername($guestProfileID);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);
        $profile->setCompany($company);
        $profile->addRole($this->getGuestRole());

        return $profile;
    }

    private function getGuestRole(): Role
    {
        $guestRole = $this->roleRepo->findOneBy(['sid' => Role::ROLE_GUEST]);
        if (null === $guestRole) {
            throw new RuntimeException('role not found: ' . Role::ROLE_GUEST);
        }

        return $guestRole;
    }
}
