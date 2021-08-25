<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\GuestInvitationRepository;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use App\Mealz\MealBundle\EventListener\ProfileExistsException;
use App\Mealz\MealBundle\EventListener\ToggleParticipationNotAllowedException;
use App\Mealz\MealBundle\Form\Guest\InvitationForm;
use App\Mealz\MealBundle\Entity\InvitationWrapper;
use App\Mealz\MealBundle\Service\DishService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

/**
 * Meal Controller
 */
class MealController extends BaseController
{
    private NotifierInterface $notifier;

    public function __construct(NotifierInterface $notifier)
    {
        $this->notifier = $notifier;
    }

    public function indexAction(WeekRepository $weekRepository, DishService $dishService): Response
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
     * let the currently logged in user join the given meal or accept an available offer
     *
     * @param string $date
     * @param string $dish
     * @param string $profile
     * @return JsonResponse
     */
    public function joinAction($date, $dish, $profile)
    {
        if ($this->getUser() === null) {
            return $this->ajaxSessionExpiredRedirect();
        }
        
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
        $errorCode = $this->getMealErrorCode($meal);
        if ($errorCode !== 200) {
            return new JsonResponse(null, $errorCode);
        }

        $profile = $this->checkProfile($profile);
        if ($profile === null) {
            return new JsonResponse(null, 403);
        }

        // Either the user is allowed to join a meal or the user is an admin, creating a participation for another user
        if ($this->getDoorman()->isUserAllowedToJoin($meal) === true
            || ($this->getDoorman()->isKitchenStaff() === true && $this->getProfile()->getUsername() !== $profile->getUsername())) {
            $participant = $this->createParticipation($meal, $profile);
            $this->logAddAction($meal, $participant);
            
            return $this->generateDeleteResponse($meal, $participant);
        }

        // Accepting an available offer
        if ($this->getDoorman()->isOfferAvailable($meal) === true && $this->getDoorman()->isUserAllowedToSwap($meal) === true) {
            $translator = new Translator('en_EN');
            $dateTime = $meal->getDateTime();
            $counter = count($this->getParticipantRepository()->getPendingParticipants($dateTime)) - 1;

            $newParticipant = $this->swapMeal($meal, $profile, $translator, $counter);
            return $this->generateAcceptResponse($meal, $newParticipant);
        }
    }

    /**
     * Checks and gets the profile when required
     *
     * @param Profile|null $profile The profile
     *
     * @return Profile|null The profile or return.
     */
    private function checkProfile($profile)
    {
        if ($profile === null) {
            $profile = $this->getProfile();
            return $profile;
        } elseif ($this->getProfile()->getUsername() === $profile || $this->getDoorman()->isKitchenStaff() === true) {
            $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
            $profile = $profileRepository->find($profile);
            return $profile;
        }

        return null;
    }

    /**
     * Returns an error code when meal is not a valid
     *
     * @param mixed $meal
     *
     * @return int
     */
    private function getMealErrorCode($meal)
    {
        if ($meal === null) {
            return 404;
        }

        if ($this->getDoorman()->isUserAllowedToJoin($meal) === false
            && $this->getDoorman()->isUserAllowedToSwap($meal) === false
            && $this->getDoorman()->isKitchenStaff() === false) {
            return 403;
        }

        return 200;
    }

    /**
     * Generates delete Ajax Request for join Action
     *
     * @param Meal        $meal         The meal
     * @param Participant $participant  The participant
     *
     * @return JsonResponse Ajax-Request
     */
    private function generateDeleteResponse($meal, $participant)
    {
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(
            array(
                'participantsCount' => $meal->getParticipants()->count(),
                'url' => $this->generateUrl(
                    'MealzMealBundle_Participant_delete',
                    array(
                        'participant' => $participant->getId(),
                    )
                ),
                'actionText' => $this->get('translator')->trans('added', array(), 'action'),
            )
        );
        return $ajaxResponse;
    }

    /**
     * Generates accept Ajax Request for join Action
     *
     * @param Meal    $meal
     * @param Participant $participant
     *
     * @return JsonResponse Ajax-Request
     */
    private function generateAcceptResponse($meal, $participant)
    {
        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(
            array(
                'participantsCount' => $meal->getParticipants()->count(),
                'url' => $this->generateUrl(
                    'MealzMealBundle_Participant_swap',
                    array(
                        'participant' => $participant->getId(),
                    )
                ),
                'actionText' => $this->get('translator')->trans('added', array(), 'actions'),
            )
        );

        return $ajaxResponse;
    }

    /**
     * Swap meal owner and send notification
     *
     * @param Meal       $meal        The meal
     * @param Profile    $profile     The profile
     * @param \Symfony\Component\Translation\TranslatorBagInterface $translator The translator
     * @param Integer    $counter     The counter
     *
     * @return Participant $participant
     */
    private function swapMeal($meal, $profile, $translator, $counter)
    {
        $takenOffer = $meal->getDish()->getTitleEn();
        if ($meal->getDish()->getParent() !== null) {
            $takenOffer = $meal->getDish()->getParent()->getTitleEn() . ' ' . $meal->getDish()->getTitleEn();
        }

        $participants = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $offeredMeal = $participants->findByOffer($meal->getId());
        $participant = $offeredMeal[0];

        // send message to meal giver
        $this->sendMail($participant, $takenOffer);

        $participant->setProfile($profile);
        $participant->setOfferedAt(0);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // send mattermost message
        $this->sendMattermostMessage(
            $translator->transChoice(
                $this->get('translator')->trans('mattermost.offer_taken', array(), 'messages'),
                $counter,
                array(
                    '%counter%' => $counter,
                    '%takenOffer%' => $takenOffer
                )
            )
        );

        return $participant;
    }

    /**
     * Returns swappable meals in an array.
     * Marks meals that are being offered.
     * @return JsonResponse
     */
    public function updateOffersAction()
    {
        $mealsArray = array();
        $meals = $this->getDoctrine()->getRepository('MealzMealBundle:Meal')->getFutureMeals();

        // Adds meals that can be swapped into $mealsArray. Marks a meal as "true", if there's an available offer for it.
        foreach ($meals as $meal) {
            if ($this->getDoorman()->isUserAllowedToSwap($meal) === true) {
                $mealsArray[$meal->getId()] =
                array(
                    $this->getDoorman()->isOfferAvailable($meal),
                    date_format($meal->getDateTime(), 'Y-m-d'),
                    $meal->getDish()->getSlug()
                );
            }
        }

        $ajaxResponse = new JsonResponse();
        $ajaxResponse->setData(
            $mealsArray
        );
        return $ajaxResponse;
    }

    /**
     * create an Emtpy Non Persistent Week (for empty Weeks)
     * @param DateTime $dateTime
     * @return Week
     */
    public function guestAction(Request $request, $hash)
    {
        $guestInvitationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:GuestInvitation');
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

        $translator = $this->get('translator');
        $form->handleRequest($request);
        $formData = $request->request->get('invitation_form');

        if (isset($formData['day']['meals']) === false || count($formData['day']['meals']) == 0) {
            $message = $translator->trans("error.participation.no_meal_selected", [], 'messages');
            $this->addFlashMessage($message, 'danger');

            return $this->renderGuestForm($form);
        }

        if ($form->isValid() === false) {
            return $this->renderGuestForm($form);
        }

        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $meals = $formData['day']['meals'];
        $mealDateTime = $mealRepository->find($meals[0])->getDateTime()->format('Y-m-d');

        $profile = $invitationWrapper->getProfile();
        $profileId = $profile->getFirstName() . $profile->getName() . $mealDateTime;
        // Try to load already existing profile entity.
        $loadedProfile = $this->getDoctrine()->getRepository('MealzUserBundle:Profile')->find($profileId);

        try {
            // If profile already exists: use it. Otherwise create new one.
            if ($loadedProfile !== null) {
                // If profile exists, but has no guest role, throw an error.
                if ($loadedProfile->isGuest() !== true) {
                    throw new ProfileExistsException('This profile entity already exists');
                }
                $profile = $loadedProfile;
            } else {
                $profile->setUsername($profileId);
                $profile->addRole($this->getGuestRole());
            }

            $this->addParticipationForEveryChosenMeal($meals, $profile, $mealRepository, $translator);
        } catch (Exception $error) {
            $message = $this->getParticipantCountMessage($error, $translator);

            $this->addFlashMessage($message, 'danger');
        } finally {
            return $this->render('base.html.twig');
        }
    }

    /**
     * Render guest form
     *
     * @param Form $form
     *
     * @return string
     */
    protected function renderGuestForm($form)
    {
        return $this->render(
            'MealzMealBundle:Meal:guest.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
    }

    /**
     * Gets the participant count message.
     *
     * @param Error      $error      The error
     * @param \Symfony\Component\Translation\TranslatorBagInterface $translator The translator
     */
    private function getParticipantCountMessage($error, $translator)
    {
        $message = $translator->trans("error.unknown", [], 'messages');

        if ($error instanceof ParticipantNotUniqueException) {
            $message = $translator->trans("error.participation.not_unique", [], 'messages');
        } elseif ($error instanceof ToggleParticipationNotAllowedException) {
            $message = $translator->trans("error.meal.join_not_allowed", [], 'messages');
        } elseif ($error instanceof ProfileExistsException) {
            $message = $translator->trans("error.profile.already_exists", [], 'messages');
        }

        return $message;
    }

    /**
     * Adds a participation for every chosen meal.
     *
     * @param Meal              $meals           The meals
     * @param Profile           $profile         The profile
     * @param MealRepository    $mealRepository  The meal repository
     * @param translator        $translator      The Translator Object
     *
     * @throws ToggleParticipationNotAllowedException
     */
    private function addParticipationForEveryChosenMeal($meals, $profile, $mealRepository, $translator)
    {
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
                $participation = new Participant();
                $participation->setProfile($profile);
                $participation->setCostAbsorbed(true);
                $participation->setMeal($meal);
                $entityManager->persist($participation);
            }
            $entityManager->persist($profile);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            $message = $translator->trans("participation.successful", [], 'messages');
            $this->addFlashMessage($message, 'success');
        } catch (Exception $error) {
            $entityManager->getConnection()->rollBack();

            throw new ToggleParticipationNotAllowedException($error);
        }
    }

    /**
     * Method to read Guest role object
     * @return Role|null
     */
    public function getGuestRole()
    {
        $roleRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Role');
        $role = $roleRepository->findOneBy(array('sid' => Role::ROLE_GUEST));

        return $role ? $role : null;
    }

    /**
     * Gets the guest invitation URL.
     *
     * @param Day $mealDay Meal day for which to generate the invitation.
     * @ParamConverter("mealDay", options={"mapping": {"dayId": "id"}})
     *
     * @return JsonResponse
     */
    public function newGuestInvitationAction(Day $mealDay)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var GuestInvitationRepository $guestInvitationRepo */
        $guestInvitationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:GuestInvitation');
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

    /**
     * Insert the participant into the database
     *
     * @param Meal $meal
     * @param string $profile
     *
     * @return Participant
     */
    private function createParticipation($meal, $profile)
    {
        try {
            $participant = new Participant();
            $participant->setProfile($profile);
            $participant->setMeal($meal);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->transactional(
                function (EntityManager $entityManager) use ($participant) {
                    $entityManager->persist($participant);
                    $entityManager->flush();
                }
            );

            $entityManager->refresh($meal);

            return $participant;
        } catch (ParticipantNotUniqueException $e) {
            return new JsonResponse(null, 422);
        }
    }

    /**
     * @param DateTime $dateTime
     * @return Week
     */
    private function createEmptyNonPersistentWeek(DateTime $dateTime)
    {
        $week = new Week();
        $week->setCalendarWeek($dateTime->format('W'));
        $week->setYear($dateTime->format('o'));

        return $week;
    }

    /**
     * Log add action of staff member
     *
     * @param Meal $meal
     * @param Participant $participant
     */
    private function logAddAction($meal, $participant)
    {
        if (is_object($this->getDoorman()->isKitchenStaff()) === false) {
            return;
        }

        $logger = $this->get('monolog.logger.balance');
        $logger->addInfo(
            'admin added {profile} to {meal} (Participant: {participantId})',
            array(
                'participantId' => $participant->getId(),
                'profile' => $participant->getProfile(),
                'meal' => $meal,
            )
        );
    }

    /**
     * Send mattermost message
     *
     * @param string $message
     */
    private function sendMattermostMessage($message)
    {
        $this->notifier->sendAlert($message);
    }
}
