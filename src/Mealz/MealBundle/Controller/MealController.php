<?php

namespace Mealz\MealBundle\Controller;


use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\Guest\InvitationWrapper;
use Mealz\UserBundle\Entity\Profile;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Form\Guest\InvitationForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;

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

		if ($this->getDoorman()->isKitchenStaff()) {
			$logger = $this->get('monolog.logger.balance');
			$logger->addInfo(
				'admin added {profile} to {meal} (Participant: {participantId})',
				array(
					"participantId" => $participant->getId(),
					"profile" => $participant->getProfile(),
					"meal" => $meal
				)
			);
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

	/**
	 * Action for inviting the guest
	 * @param $hash
	 * @return \Symfony\Component\HttpFoundation\Response
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
//VarDumper::dump($form->createView());die();

		// handle form submission
		if ($request->isMethod('POST')) {
			$form->handleRequest($request);

			if ($form->isValid()) {
				$profile = $invitationWrapper->getProfile();
                VarDumper::dump($form->getData());
			    $em = $this->getDoctrine()->getManager();
//				$em->persist($entity);
				$em->flush();

//				$this->addFlashMessage($successMessage, 'success');
			} else {
				return $this->render('MealzMealBundle:Meal:guest.html.twig', array(
						'form' => $form->createView()
				));
			}
		}
		return $this->render('MealzMealBundle:Meal:guest.html.twig', array(
				'form' => $form->createView()
		));
	}

	/**
	 * Gets the guest invitation URL.
	 *
	 * @param  Day $mealDay     Meal day for which to generate the invitation.
	 *
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
			$this->generateUrl('MealzMealBundle_Meal_guest', ['hash' => $guestInvitation->getId()]), 200
		);
	}
}
