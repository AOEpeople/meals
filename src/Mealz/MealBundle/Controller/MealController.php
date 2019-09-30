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
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Debug\Exception\ContextErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;

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
     * let the currently logged in user join the given meal or accept an available offer
     *
     * @param string $date
     * @param string $dish
     * @param string $profile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function joinAction($date, $dish, $profile)
    {
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);

        $profile = $this->getProfileOrReturn($meal, $profile);
        if ($profile instanceof JsonResponse === true) {
            return $profile;
        }

        // Either the user is allowed to join a meal or the user is an admin, creating a participation for another user
        if ($this->getDoorman()->isUserAllowedToJoin($meal) === true
            || ($this->getDoorman()->isKitchenStaff() === true && $this->getProfile()->getUsername() !== $profile->getUsername())) {
            try {
                $participant = new Participant();
                $participant->setProfile($profile);
                $participant->setMeal($meal);

                /** Insert the participant into the database. */
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->transactional(
                    function (EntityManager $entityManager) use ($participant) {
                        $entityManager->persist($participant);
                        $entityManager->flush();
                    }
                );

                $entityManager->refresh($meal);
            } catch (ParticipantNotUniqueException $e) {
                return new JsonResponse(null, 422);
            }
            return $this->generateDeleteJoinActionAjaxRequest($meal, $participant);
        }

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

        // Accepting an available offer
        if ($this->getDoorman()->isOfferAvailable($meal) === true && $this->getDoorman()->isUserAllowedToSwap($meal) === true) {
            $translator = new Translator('en_EN');
            $dateTime = $meal->getDateTime();
            $counter = count($this->getParticipantRepository()->getPendingParticipants($dateTime)) - 1;

            return $this->generateAcceptJoinActionAjaxRequest($profile, $translator, $counter);
        }
    }



    /**
     * Gets the profile or return. (Type of Doorman)
     *
     * @param Meal    $meal     The meal
     * @param Profile $profile The profile
     *
     * @return JsonResponse, Profile  The profile or return.
     */
    private function getProfileOrReturn($meal, $profile)
    {
        if ($this->getUser() === null) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($meal === null) {
            return new JsonResponse(null, 404);
        }

        if ($this->getDoorman()->isUserAllowedToJoin($meal) === false
            && $this->getDoorman()->isUserAllowedToSwap($meal) === false
            && $this->getDoorman()->isKitchenStaff() === false) {
            return new JsonResponse(null, 403);
        }

        if ($profile === null) {
            $profile = $this->getProfile();
            return $profile;
        } elseif ($this->getProfile()->getUsername() === $profile || $this->getDoorman()->isKitchenStaff() === true) {
            $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
            $profile = $profileRepository->find($profile);
            return $profile;
        } else {
            return new JsonResponse(null, 403);
        }

        return null;
    }

    /**
     * Generates delete Ajax Request for join Action
     *
     * @param Meal        $meal         The meal
     * @param Participant $participant  The participant
     *
     * @return JsonResponse Ajax-Request
     */
    private function generateDeleteJoinActionAjaxRequest($meal, $participant)
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
     * @param Profile    $profile     The profile
     * @param Translator $translator  The translator
     * @param Integer    $counter     The counter
     *
     * @return JsonResponse Ajax-Request
     */
    private function generateAcceptJoinActionAjaxRequest($profile, $translator, $counter)
    {
        if ($meal->getDish()->getParent() !== null) {
            $takenOffer = $meal->getDish()->getParent()->getTitleEn() . ' ' . $meal->getDish()->getTitleEn();
        } else {
            $takenOffer = $meal->getDish()->getTitleEn();
        }

        $participants = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $offeredMeal = $participants->findByOffer($meal->getId());
        $participant = $offeredMeal[0];

        $this->sendMail($participant, $takenOffer);

        $participant->setProfile($profile);
        $participant->setOfferedAt(0);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $chefbotMessage = $translator->transChoice(
            $this->get('translator')->trans('mattermost.offer_taken', array(), 'messages'),
            $counter,
            array(
            '%counter%' => $counter,
            '%takenOffer%' => $takenOffer
            )
        );

        $mattermostService = $this->container->get('mattermost.service');
        $mattermostService->sendMessage($chefbotMessage);

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
     * @param \DateTime $dateTime
     * @return Week
     */
    public function guestAction(Request $request, $hash)
    {
        $guestInvitationRepo =
        $this->getDoctrine()->getRepository('MealzMealBundle:GuestInvitation');
        $guestInvitation = $guestInvitationRepo->find($hash);

        if (null === $guestInvitation) {
            throw new NotFoundHttpException();
        }

        $invitationWrapper = new InvitationWrapper();
        $invitationWrapper->setDay($guestInvitation->getDay());
        $invitationWrapper->setProfile(new Profile());
        $form = $this->createForm(InvitationForm::class, $invitationWrapper);

        // handle form submission
        if ($request->isMethod('POST') !== true) {
            return;
        }

        $translator = $this->get('translator');
        $form->handleRequest($request);
        $formData = $request->request->get('invitation_form');

        if (isset($formData['day']['meals']) === false || count($formData['day']['meals']) == 0) {
            $message = $translator->trans("error.participation.no_meal_selected", [], 'messages');
            $this->addFlashMessage($message, 'danger');

            return $this->render(
                'MealzMealBundle:Meal:guest.html.twig',
                array(
                    'form' => $form->createView(),
                    )
            );
        }

        if ($form->isValid() === false) {
            return $this->render(
                'MealzMealBundle:Meal:guest.html.twig',
                [
                    'form' => $form->createView(),
                ]
            );
        }

        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $meals = $formData['day']['meals'];
        $mealDateTime = $mealRepository->find($meals[0])->getDateTime()->format('Y-m-d');

        $profile = $invitationWrapper->getProfile();
        $profileId = $profile->getFirstName() . $profile->getName() . $mealDateTime;
        // Try to load already existing profile entity.
        $loadedProfile = $this->getDoctrine()->getRepository('MealzUserBundle:Profile')->find($profileId);

        $entityManager = $this->getDoctrine()->getManager();
        // suspend auto-commit
        $entityManager->getConnection()->beginTransaction();

        try {
            // If profile already exists: use it. Otherwise create new one.
            if ($loadedProfile !== null) {
                // If profile exists, but has no guest role, throw an error.
                if ($loadedProfile->isGuest() === true) {
                    $profile = $loadedProfile;
                } else {
                    throw new ProfileExistsException('This profile entity already exists');
                }
            } else {
                $profile->setUsername($profileId);
                $profile->addRole($this->getGuestRole());
            }

            // add participation for every chosen Meal
            $this->addParticipationForEveryChosenMeal($meals, $profile, $mealRepository, $translator);
        } catch (Exception $error) {
            $entityManager->getConnection()->rollBack();

            $message = $this->getParticipantCountMessage($error, $translator);

            $this->addFlashMessage($message, 'danger');
        } finally {
            return $this->render('::base.html.twig');
        }
    }

    /**
     * Gets the participant count message.
     *
     * @param Error      $error      The error
     * @param Translator $translator The translator
     */
    private function getParticipantCountMessage($error, $translator)
    {
        if ($error instanceof ParticipantNotUniqueException) {
            $message = $translator->trans("error.participation.not_unique", [], 'messages');
        } elseif ($error instanceof ToggleParticipationNotAllowedException) {
            $message = $translator->trans("error.meal.join_not_allowed", [], 'messages');
        } elseif ($error instanceof ProfileExistsException) {
            $message = $translator->trans("error.profile.already_exists", [], 'messages');
        } else {
            $message = $translator->trans("error.unknown", [], 'messages');
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
     * @throws \Mealz\MealBundle\EventListener\ToggleParticipationNotAllowedException
     */
    private function addParticipationForEveryChosenMeal($meals, $profile, $mealRepository, $translator)
    {
        foreach ($meals as $mealId) {
            $meal = $mealRepository->find($mealId);
            // If guest enrolls too late, throw access denied error
            if ($meal->isParticipationLimitReached() === true ||
                        $this->getDoorman()->isToggleParticipationAllowed($meal->getDateTime()) === false) {
                throw new ToggleParticipationNotAllowedException();
            }
            if ($this->getDoorman()->isToggleParticipationAllowed($meal->getDay()->getLockParticipationDateTime()) === false ||
                        $this->getDoorman()->isToggleParticipationAllowed($meal->getDay()->getLockParticipationDateTime()) === null) {
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

        /** @var \Mealz\MealBundle\Entity\GuestInvitationRepository $guestInvitationRepo */
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

    private function createEmptyNonPersistentWeek(\DateTime $dateTime)
    {
        $week = new Week();
        $week->setCalendarWeek($dateTime->format('W'));
        $week->setYear($dateTime->format('Y'));

        return $week;
    }
}
