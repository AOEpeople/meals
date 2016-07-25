<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\MealProfileForm;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Mealz\MealBundle\Entity\Week;

class MealController extends BaseController {

	public function indexAction() {
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

		return $this->render('MealzMealBundle:Meal:index.html.twig', array(
			'weeks' => $weeks
		));
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
	public function joinAction(Request $request, $date, $dish, $profile) {

		if(!$this->getUser()) {
			return $this->ajaxSessionExpiredRedirect();
		}

		/** @var Meal $meal */
		$meal = $this->getMealRepository()->findOneByDateAndDish($date, $dish);

		if(!$meal) {
			return new JsonResponse(null, 404);
		}

		if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
			return new JsonResponse(null, 403);
		}

		if (null === $profile) {
			$profile = $this->getProfile();
		} else if ($this->getProfile()->getUsername() === $profile || $this->getDoorman()->isKitchenStaff()) {
			$profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
			$profile = $profileRepository->find($profile);
		} else {
			return new JsonResponse(null, 403);
		}

		try {
			$participant = new Participant();
			$participant->setProfile($profile);
			$participant->setMeal($meal);

			$em = $this->getDoctrine()->getManager();
			$em->transactional(function (EntityManager $em) use ($participant) {
				$em->persist($participant);
				$em->flush();
			});
		} catch (ParticipantNotUniqueException $e) {
			return new JsonResponse(null, 422);
		}

		$ajaxResponse = new JsonResponse();
		$ajaxResponse->setData(array(
			'participantsCount' => $meal->getParticipants()->count(),
			'url' => $this->generateUrl('MealzMealBundle_Participant_delete', array(
				'participant' => $participant->getId()
			)),
			'actionText' => $this->get('translator')->trans('added', array(), 'action')
		));

		return $ajaxResponse;
	}

	private function createEmptyNonPersistentWeek(\DateTime $dateTime)
	{
		$week = new Week();
		$week->setCalendarWeek($dateTime->format('W'));
		$week->setYear($dateTime->format('Y'));
		return $week;
	}
}