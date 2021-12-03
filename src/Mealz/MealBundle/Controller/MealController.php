<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\GuestInvitationRepository;
use App\Mealz\MealBundle\Entity\InvitationWrapper;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\SlotRepository;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Form\Guest\InvitationForm;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\Exception\ParticipationException;
use App\Mealz\MealBundle\Service\GuestParticipationService;
use App\Mealz\MealBundle\Service\Mailer;
use App\Mealz\MealBundle\Service\MealService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MealController extends BaseController
{
    private Mailer $mailer;
    private NotifierInterface $notifier;

    public function __construct(
        Mailer $mailer,
        NotifierInterface $notifier)
    {
        $this->mailer = $mailer;
        $this->notifier = $notifier;
    }

    public function index(
        DishService $dishService,
        MealService $mealService,
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
            'dishService' => $dishService,
            'mealService' => $mealService,
            'participationService' => $participationService,
            'weeks' => [$currentWeek, $nextWeek],
            'slots' => $slotRepo->findBy(['disabled' => 0, 'deleted' => 0]),
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
        SlotRepository $slotRepo
    ): JsonResponse {
        $userProfile = $this->checkProfile($profile);
        if (null === $userProfile) {
            return new JsonResponse(null, 403);
        }

        $slot = null;
        $slotSlug = $request->request->get('slot', null);
        if (null !== $slotSlug) {
            $slot = $slotRepo->findOneBy(['slug' => $slotSlug]);
        }

        try {
            $out = $participationSrv->join($userProfile, $meal, $slot);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        if (null === $out) {
            return new JsonResponse(null, 404);
        }

        if (null !== $out['offerer']) {
            $remainingOfferCount = $participationSrv->getOfferCount($meal->getDateTime());
            $this->sendMealTakenNotifications($out['offerer'], $meal, $remainingOfferCount);

            return $this->generateResponse('MealzMealBundle_Participant_swap', 'added', $meal, $out['participant']);
        }

        $this->logAdd($meal, $out['participant']);

        return $this->generateResponse('MealzMealBundle_Participant_delete', 'deleted', $meal, $out['participant']);
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

    private function generateResponse(string $route, string $action, Meal $meal, Participant $participant): JsonResponse
    {
        return new JsonResponse([
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl(
                $route,
                [
                    'participant' => $participant->getId(),
                ]
            ),
            'actionText' => $action,
        ]);
    }

    private function sendMealTakenNotifications(Profile $offerer, Meal $meal, int $remainingOfferCount): void
    {
        $dish = $meal->getDish();
        $dishTitle = $dish->getTitleEn();
        $parentDish = $dish->getParent();

        if (null !== $parentDish) {
            $dishTitle = $parentDish->getTitleEn().' '.$dishTitle;
        }

        $this->sendMealTakenEmail($offerer, $dishTitle);

        $this->sendMealTakenMattermostMsg($dishTitle, $remainingOfferCount);
    }

    private function sendMealTakenEmail(Profile $profile, string $dishTitle): void
    {
        $translator = $this->get('translator');

        $recipient = $profile->getUsername().$translator->trans('mail.domain', [], 'messages');
        $subject = $translator->trans('mail.subject', [], 'messages');

        $message = $translator->trans(
            'mail.message',
            [
                '%firstname%' => $profile->getFirstname(),
                '%takenOffer%' => $dishTitle,
            ],
            'messages'
        );

        $this->mailer->sendMail($recipient, $subject, $message);
    }

    private function sendMealTakenMattermostMsg(string $dishTitle, int $remainingOfferCount): void
    {
        $this->notifier->sendAlert(
            $this->get('translator')->trans(
                'mattermost.offer_taken',
                [
                    '%count%' => $remainingOfferCount,
                    '%counter%' => $remainingOfferCount,
                    '%takenOffer%' => $dishTitle,
                ],
                'messages'
            )
        );
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

    /**
     * @ParamConverter("invitation", options={"id" = "hash"})
     */
    public function joinAsGuest(Request $request, GuestInvitation $invitation, GuestParticipationService $gps): Response
    {
        $form = $this->getGuestInvitationForm($invitation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                ['profile' => $profile, 'meals' => $meals, 'slot' => $slot] = $this->validateGetGuestInvitationData($form);
                $gps->join($profile, $meals, $slot);

                $message = $this->get('translator')->trans('participation.successful', [], 'messages');
                $this->addFlashMessage($message, 'success');

                return $this->render('base.html.twig');
            } catch (ParticipationException $pex) {
                $this->addFlashMessage($this->exceptionToError($pex), 'danger');
            } catch (Exception $ex) {
                $this->logException($ex, 'guest registration error');
                $this->addFlashMessage($this->exceptionToError($ex), 'danger');
            }
        }

        return $this->render('MealzMealBundle:Meal:guest.html.twig', ['form' => $form->createView()]);
    }

    private function getGuestInvitationForm(GuestInvitation $invitation): FormInterface
    {
        $invitationWrapper = new InvitationWrapper();
        $invitationWrapper->setDay($invitation->getDay());
        $invitationWrapper->setProfile(new Profile());

        return $this->createForm(InvitationForm::class, $invitationWrapper);
    }

    /**
     * @throws ParticipationException
     */
    private function validateGetGuestInvitationData(FormInterface $form): array
    {
        $data = [
            'profile' => $form->get('profile')->getData(),
            'meals' => $form->get('day')->get('meals')->getData(),
            'slot' => $form->get('slot')->getData(),
        ];

        if ((null === $data['meals']) || (0 === count($data['meals']))) {
            throw new ParticipationException('invalid data', ParticipationException::ERR_GUEST_REG_MEAL_NOT_FOUND);
        }

        return $data;
    }

    private function exceptionToError(Exception $exception): string
    {
        $translator = $this->get('translator');

        if ($exception instanceof ParticipationException) {
            switch ($exception->getCode()) {
                case ParticipationException::ERR_GUEST_REG_MEAL_NOT_FOUND:
                    return $translator->trans('error.participation.no_meal_selected', [], 'messages');
                case ParticipationException::ERR_MEAL_NOT_BOOKABLE:
                    /** @var Meal $unbookableMeal */
                    $unbookableMeal = $exception->getContext()['meal'];

                    return $translator->trans(
                        'error.meal.join_not_allowed',
                        ['%dish%' => $unbookableMeal->getDish()->getTitle()],
                        'messages'
                    );
            }
        }

        return $translator->trans('error.unknown', [], 'messages');
    }

    /**
     * @param Day $mealDay meal day for which to generate the invitation
     * @ParamConverter("mealDay", options={"mapping": {"dayId": "id"}})
     *
     * @Security("is_granted('ROLE_USER')")
     */
    public function newGuestInvitation(Day $mealDay): JsonResponse
    {
        /** @var GuestInvitationRepository $guestInvitationRepo */
        $guestInvitationRepo = $this->getDoctrine()->getRepository(GuestInvitation::class);
        $guestInvitation = $guestInvitationRepo->findOrCreateInvitation($this->getUser()->getProfile(), $mealDay);

        return new JsonResponse(
            $this->generateUrl(
                'MealzMealBundle_Meal_guest',
                ['hash' => $guestInvitation->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            200
        );
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
