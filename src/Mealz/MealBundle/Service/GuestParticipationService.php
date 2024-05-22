<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\GuestInvitationRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepositoryInterface;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class GuestParticipationService
{
    use ParticipationServiceTrait;

    private EntityManagerInterface $entityManager;
    private ParticipantRepositoryInterface $participantRepo;
    private ProfileRepositoryInterface $profileRepo;
    private RoleRepositoryInterface $roleRepo;
    private SlotRepositoryInterface $slotRepo;
    private GuestInvitationRepositoryInterface $guestInvitationRepo;
    private MealRepositoryInterface $mealRepo;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParticipantRepositoryInterface $participantRepo,
        ProfileRepositoryInterface $profileRepo,
        RoleRepositoryInterface $roleRepo,
        SlotRepositoryInterface $slotRepo,
        GuestInvitationRepositoryInterface $guestInvitationRepo,
        MealRepositoryInterface $mealRepo
    ) {
        $this->entityManager = $entityManager;
        $this->participantRepo = $participantRepo;
        $this->profileRepo = $profileRepo;
        $this->roleRepo = $roleRepo;
        $this->slotRepo = $slotRepo;
        $this->guestInvitationRepo = $guestInvitationRepo;
        $this->mealRepo = $mealRepo;
    }

    /**
     * @return Participant[]
     *
     * @throws ParticipationException
     */
    public function join(Profile $profile, Collection $meals, ?Slot $slot = null, array $dishSlugs = []): array
    {
        $mealDate = $meals->first()->getDateTime();

        $guestProfile = $this->getCreateGuestProfile(
            $profile->getFirstName(),
            $profile->getName(),
            $profile->getCompany(),
            $mealDate
        );

        if (null === $slot || false === $this->slotIsAvailable($slot, $mealDate)) {
            $slot = $this->getNextFreeSlot($mealDate);
        }

        return $this->register($guestProfile, $meals, $slot, $dishSlugs);
    }

    public function getCreateGuestProfile(
        string $firstName,
        string $lastName,
        string $company,
        DateTime $mealDate
    ): Profile {
        $guestProfileID = sprintf('%s.%s_%s', $firstName, $lastName, $mealDate->format('Y-m-d'));
        $guestProfile = $this->profileRepo->find($guestProfileID);
        if (true === ($guestProfile instanceof Profile) && true === $guestProfile->isGuest()) {
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

    /**
     * Registers user with $profile to given meals and slot.
     *
     * @param Collection<int, Meal> $meals
     *
     * @return Participant[]
     *
     * @throws ParticipationException
     * @throws Exception
     */
    private function register(Profile $profile, Collection $meals, ?Slot $slot = null, array $dishSlugs = []): array
    {
        $this->validateBookableMeals($meals, $dishSlugs);

        $this->entityManager->beginTransaction();

        try {
            $this->entityManager->persist($profile);
            $participants = $this->create($profile, $meals, $slot, $dishSlugs);

            $this->entityManager->flush();
            $this->entityManager->commit();

            return $participants;
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
    private function validateBookableMeals(Collection $meals, array $dishSlugs = []): void
    {
        $flippedDishSlugs = array_flip($dishSlugs);

        $participations = [];

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            if (true === empty($participations)) {
                $participations = (new ParticipationCountService())->getParticipationByDay($meal->getDay());
            }

            $bookable = $this->mealIsBookable($meal);
            if (false === $bookable) {
                throw new ParticipationException('meal not bookable', ParticipationException::ERR_MEAL_NOT_BOOKABLE, null, ['meal' => $meal]);
            }

            $dishSlugArray = [$meal->getDish()->getSlug()];
            $participationCount = 1.0;
            if (true === $meal->getDish()->isCombinedDish()) {
                $dishSlugArray = $dishSlugs;
                $participationCount = 0.5;
            } else {
                // Note: There is an edge case, when a guest books a meal with limitation and a combined meal at once
                if (true === isset($flippedDishSlugs[$meal->getDish()->getSlug()])) {
                    $participationCount = 1.5;
                }
            }

            if (false === ParticipationCountService::isParticipationPossibleForDishes($participations[ParticipationCountService::PARTICIPATION_TOTAL_COUNT_KEY], $dishSlugArray, $participationCount)) {
                throw new ParticipationException('meal not bookable', ParticipationException::ERR_MEAL_NOT_BOOKABLE, null, ['meal' => $meal, 'bookedCombinedDishes' => $dishSlugs]);
            }
        }
    }

    /**
     * Create guest participation.
     *
     * @param Collection<int, Meal> $meals
     *
     * @return Participant[]
     *
     * @throws ParticipationException
     *
     * @psalm-return list<Participant>
     */
    private function create(Profile $profile, Collection $meals, ?Slot $slot = null, array $dishSlugs = []): array
    {
        $participants = [];

        foreach ($meals as $meal) {
            try {
                $participant = $this->createParticipation($profile, $meal, $slot, $dishSlugs);
            } catch (ParticipationException $pex) {
                $pex->addContext(['operation' => 'guest participation create']);
                throw $pex;
            }

            $participant->setCostAbsorbed(true);
            $this->entityManager->persist($participant);
            $participants[] = $participant;

            $meal->participants->add($participant);
        }

        return $participants;
    }

    private function getGuestRole(): Role
    {
        $guestRole = $this->roleRepo->findOneBy(['sid' => Role::ROLE_GUEST]);
        if (null === $guestRole) {
            throw new RuntimeException('role not found: ' . Role::ROLE_GUEST);
        }

        return $guestRole;
    }

    public function getGuestInvitationById(string $guestInvitationId): ?GuestInvitation
    {
        return $this->guestInvitationRepo->find($guestInvitationId);
    }

    /**
     * @return (mixed|object|null)[]
     *
     * @throws ParticipationException
     * @throws Exception
     *
     * @psalm-return array{profile: Profile, meals: MealCollection, slot: null|object, dishSlugs: mixed}
     */
    public function getGuestInvitationData(Request $request): array
    {
        $parameters = json_decode($request->getContent(), true);

        $meals = new MealCollection();

        foreach ($parameters['chosenMeals'] as $mealId) {
            $meal = $this->mealRepo->find((int) $mealId);

            if (null === $meal) {
                $meals = null;
                break;
            }
            $meals->add($meal);
        }

        if ((null === $meals) || (0 === count($meals))) {
            throw new ParticipationException('invalid data', ParticipationException::ERR_GUEST_REG_MEAL_NOT_FOUND);
        }

        $dishSlugs = $parameters['combiDishes'];

        $slot = null;
        if (0 !== $parameters['chosenSlot']) {
            $slot = $this->slotRepo->find($parameters['chosenSlot']);
        }

        $profile = new Profile();
        $profile->setName($parameters['lastName']);
        $profile->setFirstName($parameters['firstName']);
        $profile->setCompany($parameters['company']);

        return [
            'profile' => $profile,
            'meals' => $meals,
            'slot' => $slot,
            'dishSlugs' => $dishSlugs,
        ];
    }
}
