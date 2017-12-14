<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use Exception;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\EventListener\ProfileExistsException;
use Mealz\MealBundle\EventListener\ToggleParticipationNotAllowedException;
use Mealz\MealBundle\Form\Guest\InvitationForm;
use Mealz\MealBundle\Entity\InvitationWrapper;
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;


/**
 * Meal Controller
 */
class MealController extends BaseController
{

    /**
     * the index Action
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');

        $currentWeek = $weekRepository->getCurrentWeek();
        if (null === $currentWeek) {
            $currentWeek = $this->createEmptyNonPersistentWeek(new \DateTime());
        }

        $nextWeek = $weekRepository->getNextWeek();
        if (null === $nextWeek) {
            $nextWeek = $this->createEmptyNonPersistentWeek(new \DateTime('next week'));
        }

        $weeks = array($currentWeek, $nextWeek);

        return $this->render(
            'MealzMealBundle:Meal:index.html.twig',
            array(
                'weeks' => $weeks,
            )
        );
    }

    /**
     * let the currently logged in user join the given meal
     *
     * @param Request $request
     * @param string $date
     * @param string $dish
     * @param string $profile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function joinAction(Request $request, $date, $dish, $profile)
    {

        if (!$this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        /** @var Meal $meal */
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);

        if (!$meal) {
            return new JsonResponse(null, 404);
        }
        /** Participation forbidden, if user is not allowed to join. */
        if (!$this->getDoorman()->isUserAllowedToJoin($meal)) {
            return new JsonResponse(null, 403);
        }

        /** Get profile of user. If there is no profile available, forbid to join. */
        if (null === $profile) {
            $profile = $this->getProfile();
        } else {
            if ($this->getProfile()->getUsername() === $profile || $this->getDoorman()->isKitchenStaff() === true) {
                $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
                $profile = $profileRepository->find($profile);
            } else {
                return new JsonResponse(null, 403);
            }
        }

        /** Create new participant using the profile and the chosen meal. */
        try {
            $participant = new Participant();
            $participant->setProfile($profile);
            $participant->setMeal($meal);

            /** Insert the participant into the database. */
            $em = $this->getDoctrine()->getManager();
            $em->transactional(
                function (EntityManager $em) use ($participant) {
                    $em->persist($participant);
                    $em->flush();
                }
            );
            $em->refresh($meal);
        } catch (ParticipantNotUniqueException $e) {
            return new JsonResponse(null, 422);
        }

        /** If the user is a kitchen staff, he can add a profile to a meal. */
        if (is_object($this->getDoorman()->isKitchenStaff()) === true) {
            $logger = $this->get('monolog.logger.balance');
            $logger->addInfo(
                'admin added {profile} to {meal} (Participant: {participantId})',
                array(
                    "participantId" => $participant->getId(),
                    "profile" => $participant->getProfile(),
                    "meal" => $meal,
                )
            );
        }

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
     * @param $date
     * @param $dish
     * @param $profile
     * @return JsonResponse
     */
    public function acceptOfferAction($date, $dish, $profile)
    {
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);
        $dateTime = $meal->getDateTime();
        $counter = $this->countMeals($dateTime)-1;

        $translator = new Translator('en_EN');
        $translator->addLoader('array', new ArrayLoader());

        if (!$this->getDoorman()->isOfferAvailable($meal)) {
            return new JsonResponse(null, 403);
        }

        if (!$this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if (!$this->getDoorman()->isUserAllowedToSwap($meal)) {
            return new JsonResponse(null, 403);
        }

        if (!$meal) {
            return new JsonResponse(null, 404);
        }

        if (null === $profile) {
            $profile = $this->getProfile();
        } else {
            if ($this->getProfile()->getUsername() === $profile || $this->getDoorman()->isKitchenStaff() === true) {
                $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
                $profile = $profileRepository->find($profile);
            } else {
                return new JsonResponse(null, 403);
            }
        }

        if ($meal->getDish()->getParent()) {
            $takenOffer = $meal->getDish()->getParent()->getTitleEn() . ' ' . $meal->getDish()->getTitleEn();
        } else {
            $takenOffer = $meal->getDish()->getTitleEn();
        }

        $participants = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $offeredMeal = $participants->findByOffer($meal->getId());
        $participant = $offeredMeal[0];

        $to = $participant->getProfile()->getUsername() . '@aoe.com';
        $subject = 'Your offered food has been taken';
        $header = 'From: AOE Meals Chef Bot <noreply-meals@aoe.com';
        $firstname = $participant->getProfile()->getFirstname();

        $message = 'Hi, ' . $firstname . ', I\'d just like to inform you that the ' . $takenOffer . ' you offered has just been taken by someone.
        No further action is required on your side. Cheers, Your Chef Bot.';
        $message = wordwrap($message,70);

        mail($to, $subject, $message, $header);

        $participant->setProfile($profile);
        $participant->setOfferedAt(0);

        $em = $this->getDoctrine()->getManager();
        $em->flush();


        $translator = new Translator('en_EN');
        $translator->addLoader('array', new ArrayLoader());

        $chefbotMessage = $translator->transChoice('{0} One "%takenOffer%" has been taken. All offers gone now.|
        {1} One "%takenOffer%" has been taken. Still %counter% other meal being offered.|
        [2, Inf[ One "%takenOffer%" has been taken. Still %counter% other meals being offered.',
            $counter, array(
                '%counter%' => $counter,
                '%takenOffer%' => $takenOffer)
        );

        $this->slack($chefbotMessage);

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
                'actionText' => $this->get('translator')->trans('added', array(), 'action'),
            )
        );

        return $ajaxResponse;

    }

    /**
     * @return JsonResponse
     */
    public function getSwappableMealsAction() {
        $mealsArray = array();
        $dayRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Day');
        $todayAndTomorrow = $dayRepository->getTodayAndTomorrow();

        foreach ($todayAndTomorrow as $day) {
            $meals = $this->getMealRepository()->findBy(['day' => $day->getId()]);

            foreach ($meals as $meal) {
                $mealsArray[$meal->getId()] = array($this->getDoorman()->isOfferAvailable($meal) && $this->getDoorman()->isUserAllowedToSwap($meal),
                    date_format($meal->getDateTime(), 'Y-m-d'), $meal->getDish()->getSlug());
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
     * @param \DateTime $dateTime
     * @return Week
     */
    public function guestAction(Request $request, $hash)
    {
        $guestInvitationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:GuestInvitation');
        $guestInvitation = $guestInvitationRepository->find($hash);

        if (null === $guestInvitation) {
            throw new NotFoundHttpException();
        }

        $invitationWrapper = new InvitationWrapper();
        $invitationWrapper->setDay($guestInvitation->getDay());
        $invitationWrapper->setProfile(new Profile());
        $form = $this->createForm(InvitationForm::class, $invitationWrapper);

        // handle form submission
        if ($request->isMethod('POST')) {
            $translator = $this->get('translator');
            $form->handleRequest($request);
            $formData = $request->request->get('invitation_form');

            if (!isset($formData['day']['meals']) || count($formData['day']['meals']) == 0) {
                $message = $translator->trans("error.participation.no_meal_selected", [], 'messages');
                $this->addFlashMessage($message, 'danger');
            } elseif ($form->isValid()) {
                $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
                $meals = $formData['day']['meals'];
                $mealDateTime = $mealRepository->find($meals[0])->getDateTime()->format('Y-m-d');

                $profile = $invitationWrapper->getProfile();
                $profileId = $profile->getFirstName() . $profile->getName() . $mealDateTime;
                // Try to load already existing profile entity.
                $loadedProfile = $this->getDoctrine()->getRepository('MealzUserBundle:Profile')->find($profileId);

                $em = $this->getDoctrine()->getManager();
                $em->getConnection()->beginTransaction(); // suspend auto-commit
                try {
                    // If profile already exists: use it. Otherwise create new one.
                    if ($loadedProfile) {
                        // If profile exists, but has no guest role, throw an error.
                        if ($loadedProfile->isGuest()) {
                            $profile = $loadedProfile;
                        } else {
                            throw new ProfileExistsException('This profile entity already exists');
                        }
                    } else {
                        $profile->setUsername($profileId);
                        $profile->addRole($this->getGuestRole());
                    }

                    // add participation for every chosen Meal
                    foreach ($meals as $mealId) {
                        $meal = $mealRepository->find($mealId);
                        // If guest enrolls too late, throw access denied error
                        if ($meal->isParticipationLimitReached() || !$this->getDoorman()->isToggleParticipationAllowed($meal->getDateTime())) {
                            throw new ToggleParticipationNotAllowedException();
                        }
                        if (!$this->getDoorman()->isToggleParticipationAllowed($meal->getDay()->getLockParticipationDateTime())) {
                            throw new ToggleParticipationNotAllowedException();
                        }
                        $participation = new Participant();
                        $participation->setProfile($profile);
                        $participation->setCostAbsorbed(true);
                        $participation->setMeal($meal);
                        $em->persist($participation);
                    }
                    $em->persist($profile);
                    $em->flush();
                    $em->getConnection()->commit();
                    $message = $translator->trans("participation.successful", [], 'messages');
                    $this->addFlashMessage($message, 'success');
                } catch (Exception $e) {
                    $em->getConnection()->rollBack();

                    if ($e instanceof ParticipantNotUniqueException) {
                        $message = $translator->trans("error.participation.not_unique", [], 'messages');
                    } elseif ($e instanceof ToggleParticipationNotAllowedException) {
                        $message = $translator->trans("error.meal.join_not_allowed", [], 'messages');
                    } elseif ($e instanceof ProfileExistsException) {
                        $message = $translator->trans("error.profile.already_exists", [], 'messages');
                    } else {
                        $message = $translator->trans("error.unknown", [], 'messages');
                    }

                    $this->addFlashMessage($message, 'danger');
                } finally {
                    return $this->render('::base.html.twig');
                }
            }
        }

        return $this->render(
            'MealzMealBundle:Meal:guest.html.twig',
            array(
                'form' => $form->createView(),
            )
        );
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
     * @param  Day $mealDay Meal day for which to generate the invitation.
     * @ParamConverter("mealDay", options={"mapping": {"dayId": "id"}})
     *
     * @return JsonResponse
     */
    public function newGuestInvitationAction(Day $mealDay)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var \Mealz\MealBundle\Entity\GuestInvitationRepository $guestInvitationRepository */
        $guestInvitationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:GuestInvitation');
        $guestInvitation = $guestInvitationRepository->findOrCreateInvitation($this->getUser()->getProfile(), $mealDay);

        return new JsonResponse(
            $this->generateUrl(
                'MealzMealBundle_Meal_guest',
                ['hash' => $guestInvitation->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
            200
        );
    }

    private function createEmptyNonPersistentWeek(\DateTime $dateTime)
    {
        $week = new Week();
        $week->setCalendarWeek($dateTime->format('W'));
        $week->setYear($dateTime->format('Y'));

        return $week;
    }
}
