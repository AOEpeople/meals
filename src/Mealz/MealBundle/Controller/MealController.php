<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\GuestInvitationRepository;
use App\Mealz\MealBundle\Entity\InvitationWrapper;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use App\Mealz\MealBundle\EventListener\ProfileExistsException;
use App\Mealz\MealBundle\EventListener\ToggleParticipationNotAllowedException;
use App\Mealz\MealBundle\Form\Guest\InvitationForm;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\Mailer;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class MealController extends BaseController
{
    private Mailer $mailer;
    private NotifierInterface $notifier;

    public function __construct(
        Mailer            $mailer,
        NotifierInterface $notifier)
    {
        $this->mailer = $mailer;
        $this->notifier = $notifier;
    }

    public function index(WeekRepository $weekRepository, DishService $dishService): Response
    {
        $currentWeek = $weekRepository->getCurrentWeek();
        if (null === $currentWeek) {
            $currentWeek = $this->createEmptyNonPersistentWeek(new DateTime());
        }

        $nextWeek = $weekRepository->getNextWeek();
        if (null === $nextWeek) {
            $nextWeek = $this->createEmptyNonPersistentWeek(new DateTime('next week'));
        }

        return $this->render('MealzMealBundle:Meal:index.html.twig', [
            'weeks' => [$currentWeek, $nextWeek],
            'dishService' => $dishService
        ]);
    }

    /**
     * Lets the currently logged-in user either join a meal, or accept an already booked meal offered by a participant.
     */
    public function join(
        string               $date,
        string               $dish,
        ?string              $profile,
        ParticipationService $participationSrv
    ): JsonResponse
    {
        if (null === $this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
        $errorCode = $this->getMealErrorCode($meal);
        if ($errorCode !== 200) {
            return new JsonResponse(null, $errorCode);
        }

        $userProfile = $this->checkProfile($profile);
        if (null === $userProfile) {
            return new JsonResponse(null, 403);
        }

        try {
            $out = $participationSrv->join($userProfile, $meal);
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
     * Checks and gets the profile when required
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

    /**
     * Returns an error code when meal is not a valid
     *
     * @param mixed $meal
     */
    private function getMealErrorCode($meal): int
    {
        if (null === $meal) {
            return 404;
        }

        if ($this->getDoorman()->isUserAllowedToJoin($meal) === false
            && $this->getDoorman()->isUserAllowedToSwap($meal) === false
            && $this->getDoorman()->isKitchenStaff() === false) {
            return 403;
        }

        return 200;
    }

    private function generateResponse(string $route, string $action, Meal $meal, Participant $participant): JsonResponse
    {
        return new JsonResponse([
            'participantsCount' => $meal->getParticipants()->count(),
            'url' => $this->generateUrl(
                $route,
                [
                    'participant' => $participant->getId()
                ]
            ),
            'actionText' => $action
        ]);
    }

    private function sendMealTakenNotifications(Profile $offerer, Meal $meal, int $remainingOfferCount): void
    {
        $dish = $meal->getDish();
        $dishTitle = $dish->getTitleEn();
        $parentDish = $dish->getParent();

        if (null !== $parentDish) {
            $dishTitle = $parentDish->getTitleEn() . ' ' . $dishTitle;
        }

        $this->sendMealTakenEmail($offerer, $dishTitle);

        $this->sendMealTakenMattermostMsg($dishTitle, $remainingOfferCount);
    }

    private function sendMealTakenEmail(Profile $profile, string $dishTitle): void
    {
        $translator = $this->get('translator');

        $recipient = $profile->getUsername() . $translator->trans('mail.domain', [], 'messages');
        $subject = $translator->trans('mail.subject', [], 'messages');

        $message = $translator->trans(
            'mail.message',
            [
                '%firstname%' => $profile->getFirstname(),
                '%takenOffer%' => $dishTitle
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
                    '%takenOffer%' => $dishTitle
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
            if ($this->getDoorman()->isUserAllowedToSwap($meal) === true) {
                $mealsArray[$meal->getId()] =
                    [
                        $this->getDoorman()->isOfferAvailable($meal),
                        date_format($meal->getDateTime(), 'Y-m-d'),
                        $meal->getDish()->getSlug()
                    ];
            }
        }

        return new JsonResponse($mealsArray);
    }

    public function guest(Request $request, string $hash): Response
    {
        $guestInvitationRepo = $this->getDoctrine()->getRepository(GuestInvitation::class);
        $guestInvitation = $guestInvitationRepo->find($hash);

        if (null === $guestInvitation) {
            throw new NotFoundHttpException();
        }

        $invitationWrapper = new InvitationWrapper();
        $invitationWrapper->setDay($guestInvitation->getDay());
        $invitationWrapper->setProfile(new Profile());
        $form = $this->createForm(InvitationForm::class, $invitationWrapper);

        // handle form submission
        if ($request->isMethod('POST') === false) {
            return $this->renderGuestForm($form);
        }

        $form->handleRequest($request);
        $formData = $request->request->get('invitation_form');

        if (isset($formData['day']['meals']) === false || count($formData['day']['meals']) == 0) {
            $message = $this->get('translator')->trans("error.participation.no_meal_selected", [], 'messages');
            $this->addFlashMessage($message, 'danger');

            return $this->renderGuestForm($form);
        }

        if ($form->isValid() === false) {
            return $this->renderGuestForm($form);
        }

        $mealRepository = $this->getMealRepository();
        $meals = $formData['day']['meals'];
        $mealDateTime = $mealRepository->find($meals[0])->getDateTime()->format('Y-m-d');

        $profile = $invitationWrapper->getProfile();
        $profileId = $profile->getFirstName() . "." . $profile->getName() . "_" . $mealDateTime;
        // Try to load already existing profile entity.
        $loadedProfile = $this->getDoctrine()->getRepository(Profile::class)->find($profileId);

        try {
            // If profile already exists: use it. Otherwise create new one.
            if (null !== $loadedProfile) {
                // If profile exists, but has no guest role, throw an error.
                if ($loadedProfile->isGuest() !== true) {
                    throw new ProfileExistsException('This profile entity already exists');
                }
                $profile = $loadedProfile;
            } else {
                $profile->setUsername($profileId);
                $profile->addRole($this->getGuestRole());
            }

            $this->addParticipationForEveryChosenMeal($meals, $profile, $mealRepository);
        } catch (Exception $error) {
            $message = $this->getParticipantCountMessage($error);

            $this->addFlashMessage($message, 'danger');
        } finally {
            return $this->render('base.html.twig');
        }
    }

    private function renderGuestForm(FormInterface $form): Response
    {
        return $this->render('MealzMealBundle:Meal:guest.html.twig', ['form' => $form->createView()]);
    }

    private function getParticipantCountMessage(Exception $error): string
    {
        $message = $this->get('translator')->trans("error.unknown", [], 'messages');

        if ($error instanceof ParticipantNotUniqueException) {
            $message = $this->get('translator')->trans("error.participation.not_unique", [], 'messages');
        } elseif ($error instanceof ToggleParticipationNotAllowedException) {
            $message = $this->get('translator')->trans("error.meal.join_not_allowed", [], 'messages');
        } elseif ($error instanceof ProfileExistsException) {
            $message = $this->get('translator')->trans("error.profile.already_exists", [], 'messages');
        }

        return $message;
    }

    /**
     * @throws ToggleParticipationNotAllowedException
     * @throws \Doctrine\DBAL\Exception
     */
    private function addParticipationForEveryChosenMeal(array $meals, Profile $profile, MealRepository $mealRepository): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        // suspend auto-commit
        $entityManager->getConnection()->beginTransaction();

        try {
            foreach ($meals as $mealId) {
                $meal = $mealRepository->find($mealId);
                // If guest enrolls too late, throw access denied error
                if ($meal->isParticipationLimitReached() === true ||
                    $this->getDoorman()->isToggleParticipationAllowed($meal->getDateTime()) === false) {
                    throw new ToggleParticipationNotAllowedException();
                }
                if ($this->getDoorman()->isToggleParticipationAllowed($meal->getDay()->getLockParticipationDateTime()) === false) {
                    throw new ToggleParticipationNotAllowedException();
                }
                $participant = new Participant($profile, $meal);
                $participant->setCostAbsorbed(true);
                $entityManager->persist($participant);
            }
            $entityManager->persist($profile);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            $message = $this->get('translator')->trans("participation.successful", [], 'messages');
            $this->addFlashMessage($message, 'success');
        } catch (Exception $error) {
            $entityManager->getConnection()->rollBack();

            throw new ToggleParticipationNotAllowedException($error->getMessage());
        }
    }

    private function getGuestRole(): ?Role
    {
        $roleRepository = $this->getDoctrine()->getRepository(Role::class);
        return $roleRepository->findOneBy(['sid' => Role::ROLE_GUEST]);
    }

    /**
     * @param Day $mealDay Meal day for which to generate the invitation.
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
        $week->setCalendarWeek((int)$dateTime->format("W"));
        $week->setYear((int)$dateTime->format("o"));

        return $week;
    }

    /**
     * Log add action of staff member
     */
    private function logAdd(Meal $meal, Participant $participant): void
    {
        if (is_object($this->getDoorman()->isKitchenStaff()) === false) {
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
