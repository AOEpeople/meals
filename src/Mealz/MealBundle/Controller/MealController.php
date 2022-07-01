<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\SlotRepository;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealController extends BaseController
{
    private MealAvailabilityService $availabilityService;
    private OfferService $offerService;

    public function __construct(OfferService $offerService, MealAvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
        $this->offerService = $offerService;
    }

    public function index(
        DishService $dishService,
        ParticipationService $participationService,
        SlotRepository $slotRepo,
        WeekRepository $weekRepository
    ): Response {
        $currentWeek = $weekRepository->getCurrentWeek();
        if (null === $currentWeek) {
            $currentWeek = $this->createEmptyNonPersistentWeek(new DateTime());
        }

        $nextWeek = $weekRepository->getNextWeek();
        if (null === $nextWeek) {
            $nextWeek = $this->createEmptyNonPersistentWeek(new DateTime('next week'));
        }

        return $this->render('MealzMealBundle:Meal:index.html.twig', [
            'availabilityService' => $this->availabilityService,
            'dishService' => $dishService,
            'participationService' => $participationService,
            'weeks' => [$currentWeek, $nextWeek],
            'participations' => array_merge(
                ParticipationCountService::getParticipationByDays($currentWeek),
                ParticipationCountService::getParticipationByDays($nextWeek)
            ),
            'slots' => $slotRepo->findBy(['disabled' => 0, 'deleted' => 0], ['order' => 'ASC']),
        ]);
    }

    /**
     * Lets the currently logged-in user either join a meal, or accept an already booked meal offered by a participant.
     *
     * @param string $dish Dish Slug
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function join(
        Request $request,
        DateTime $date,
        string $dish,
        ?string $profile,
        MealRepositoryInterface $mealRepo,
        ParticipationService $participationSrv,
        SlotRepository $slotRepo,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        $userProfile = $this->checkProfile($profile);
        if (null === $userProfile) {
            return new JsonResponse(null, 403);
        }

        $meal = $mealRepo->findOneByDateAndDish($date, $dish);
        if (null === $meal) {
            return new JsonResponse(null, 404);
        }

        $slot = null;
        $slotSlug = $request->request->get('slot');
        if (null !== $slotSlug) {
            $slot = $slotRepo->findOneBy(['slug' => $slotSlug]);
        }

        $dishSlugs = $request->request->get('dishes', []);

        try {
            $result = $participationSrv->join($userProfile, $meal, $slot, $dishSlugs);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        if (null === $result) {
            return new JsonResponse(null, 404);
        }

        $this->triggerJoinEvents($eventDispatcher, $result['participant'], $result['offerer']);

        $participationCount = $participationSrv->getCountByMeal($meal);

        if (null === $result['offerer']) {
            $this->logAdd($meal, $result['participant']);
            $nextActionRoute = 'MealzMealBundle_Participant_delete';
        } else {
            $nextActionRoute = 'MealzMealBundle_Participant_swap';
        }

        return $this->generateResponse($nextActionRoute, 'added', $result['participant'], $participationCount);
    }

    /**
     * Checks and gets the profile when required.
     */
    private function checkProfile(?string $profileId): ?Profile
    {
        if (null === $profileId) {
            return $this->getProfile();
        }

        if (!$this->getDoorman()->isKitchenStaff()) {
            return null;
        }

        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);

        return $profileRepository->find($profileId);
    }

    private function generateResponse(
        string $route,
        string $action,
        Participant $participant,
        int $participantCount
    ): JsonResponse {
        $bookedDishSlugs = [];
        $dishes = $participant->getCombinedDishes();

        if (0 < $dishes->count()) {
            $bookedDishSlugs = array_map(fn (Dish $dish) => $dish->getSlug(), $dishes->toArray());
        }

        $meal = $participant->getMeal();
        $slot = $participant->getSlot();

        return new JsonResponse([
            'id' => $participant->getId(),
            'participantsCount' => $participantCount,
            'url' => $this->generateUrl(
                $route,
                ['participant' => $participant->getId()]
            ),
            'actionText' => $action,
            'bookedDishSlugs' => $bookedDishSlugs,
            'slot' => $slot ? $slot->getSlug() : '',
            'available' => $this->availabilityService->isAvailable($meal),
        ]);
    }

    /**
     * @param string $dish Dish Slug
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function getOffers(DateTime $date, string $dish, MealRepositoryInterface $mealRepo): JsonResponse
    {
        $meal = $mealRepo->findOneByDateAndDish($date, $dish);
        if (null === $meal) {
            return new JsonResponse(null, 404);
        }

        $offers = $this->offerService->getOffers($meal);

        if (empty($offers)) {
            return new JsonResponse(null, 404);
        }

        $translator = $this->get('translator');
        $title = $translator->trans('offer_dialog.title', [], 'messages');

        return new JsonResponse([
            'title' => $title,
            'offers' => array_values($offers),
        ]);
    }

    private function createEmptyNonPersistentWeek(DateTime $dateTime): Week
    {
        $week = new Week();
        $week->setCalendarWeek((int) $dateTime->format('W'));
        $week->setYear((int) $dateTime->format('o'));

        return $week;
    }

    /**
     * Log add action of staff member.
     */
    private function logAdd(Meal $meal, Participant $participant): void
    {
        if (false === is_object($this->getDoorman()->isKitchenStaff())) {
            return;
        }

        $logger = $this->get('monolog.logger.balance');
        $logger->info(
            'admin added {profile} to {meal} (Participant: {participantId})',
            [
                'participantId' => $participant->getId(),
                'profile' => $participant->getProfile(),
                'meal' => $meal,
            ]
        );
    }

    private function triggerJoinEvents(
        EventDispatcherInterface $eventDispatcher,
        Participant $participant,
        ?Profile $offerer
    ): void {
        if (null !== $offerer) {
            $eventDispatcher->dispatch(new MealOfferAcceptedEvent($participant, $offerer));

            return;
        }

        $eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $slot = $participant->getSlot();
        if (null !== $slot) {
            $eventDispatcher->dispatch(new SlotAllocationUpdateEvent($participant->getMeal()->getDateTime(), $slot));
        }
    }
}
