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

        $userParticipationForToday = $this->getParticipantRepository()->getParticipationForProfile($profile, $date);
        $userSelections = array();
        foreach ($userParticipationForToday as $participation) {
            if ($participation['meal']['id']) {
                $userSelections[] = $participation['meal']['id'];
            }
        }

        /** @var Meal $meal */
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish, $userSelections);

        if (is_object($meal) === false) {
            return new JsonResponse(null, 404);
        }

        if (is_object($this->getDoorman()->isUserAllowedToJoin($meal)) === false) {
            return new JsonResponse(null, 403);
        }

        try {
            $participant = new Participant();
            $participant->setProfile($profile);
            $participant->setMeal($meal);

            $em = $this->getDoctrine()->getManager();
            $em->transactional(
                function (EntityManager $em) use ($participant) {
                    $em->persist($participant);
                    $em->flush();
                }
            );
        } catch (ParticipantNotUniqueException $e) {
            return new JsonResponse(null, 422);
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
     * create an Emtpy Non Persistent Week (for empty Weeks)
     * @param \DateTime $dateTime
     * @return Week
     */
    private function createEmptyNonPersistentWeek(\DateTime $dateTime)
    {
        $week = new Week();
        $week->setCalendarWeek($dateTime->format('W'));
        $week->setYear($dateTime->format('Y'));

        return $week;
    }
}
