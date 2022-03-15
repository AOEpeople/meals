<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Event\SlotAllocationUpdateEvent;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\Mailer;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MealController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private OfferService $offerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        OfferService $offerService
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->offerService = $offerService;
    }

    public function index(
        DishService $dishService,
        MealAvailabilityService $availabilityService,
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
            'availabilityService' => $availabilityService,
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
     * @Security("is_granted('ROLE_USER')")
     * @Entity("meal", expr="repository.findOneByDateAndDish(date, dish)")
     */
    public function join(
        Request $request,
        Meal $meal,
        ?string $profile,
        ParticipationService $participationSrv,
        SlotRepository $slotRepo,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        $userProfile = $this->checkProfile($profile);
        if (null === $userProfile) {
            return new JsonResponse(null, 403);
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

        if (null !== $result['offerer']) {
            $eventDispatcher->dispatch(new MealOfferAcceptedEvent($result['participant'], $result['offerer']));

            return $this->generateResponse('MealzMealBundle_Participant_swap', 'added', $result['participant']);
        }

        $this->logAdd($meal, $result['participant']);

        return $this->generateResponse('MealzMealBundle_Participant_delete', 'added', $result['participant']);
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

    private function generateResponse(string $route, string $action, Participant $participant): JsonResponse
    {
        $bookedDishSlugs = [];
        $dishes = $participant->getCombinedDishes();

        if (0 < $dishes->count()) {
            $bookedDishSlugs = array_map(fn (Dish $dish) => $dish->getSlug(), $dishes->toArray());
        }

        $this->eventDispatcher->dispatch(new ParticipationUpdateEvent($participant));

        $meal = $participant->getMeal();
        $slot = $participant->getSlot();

        if ($slot && $slot->getLimit()) {
            $this->eventDispatcher->dispatch(new SlotAllocationUpdateEvent($slot, $meal->getDateTime()));
        }

        return new JsonResponse([
            'id' => $participant->getId(),
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl(
                $route,
                [
                    'participant' => $participant->getId(),
                ]
            ),
            'actionText' => $action,
            'bookedDishSlugs' => $bookedDishSlugs,
            'slot' => $slot ? $slot->getSlug() : '',
        ]);
    }

    /**
     * @Security("is_granted('ROLE_USER')")
     * @Entity("meal", expr="repository.findOneByDateAndDish(date, dish)")
     */
    public function getOffers(Meal $meal): JsonResponse
    {
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

    /**
     * Returns swappable meals in an array.
     * Marks meals that are being offered.
     */
    public function updateOffers(): JsonResponse
    {
        $mealsArray = [];
        $meals = $this->getMealRepository()->getFutureMeals();

        // Adds meals that can be swapped into $mealsArray. Marks a meal as "true", if there's an available offer for it.
        foreach ($meals as $meal) {
            if (true === $this->getDoorman()->isUserAllowedToSwap($meal)) {
                $mealsArray[$meal->getId()] =
                    [
                        $this->getDoorman()->isOfferAvailable($meal),
                        date_format($meal->getDateTime(), 'Y-m-d'),
                        $meal->getDish()->getSlug(),
                    ];
            }
        }

        return new JsonResponse($mealsArray);
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
}
