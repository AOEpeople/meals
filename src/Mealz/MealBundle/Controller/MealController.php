<?php


namespace Mealz\MealBundle\Controller;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Exception;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Form\Guest\InvitationWrapper;
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;
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

		// handle form submission
		if ($request->isMethod('POST')) {
			$translator = $this->get('translator');
			$form->handleRequest($request);

			if ($form->isValid()) {
				// $em instanceof EntityManager
				$em = $this->getDoctrine()->getManager();
				$profile = $invitationWrapper->getProfile();
				$profile->setUsername($profile->getName() . time());
				$profile->addRole($this->getGuestRole());

				$mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
				$formData = $request->request->get('invitation_form');
				$meals = ($formData['day']['meals'] && is_array($formData['day']['meals'])) ? $formData['day']['meals'] : null;
				$em->getConnection()->beginTransaction(); // suspend auto-commit
				try {
					// add participation for every chosen Meal
					foreach ($meals as $mealId) { /* @var $meal Meal */
						$participation = new Participant();
						$participation->setProfile($profile);
						$participation->setCostAbsorbed(true);
						$participation->setMeal($mealRepository->find($mealId));
						$em->persist($participation);
					}
					$em->persist($profile);
					$em->flush();
					$em->getConnection()->commit();
				} catch (Exception $e) {
					$em->getConnection()->rollBack();
					throw $e;
				}
				$message = $translator->trans("participation.successful", [], 'messages');

				$this->addFlashMessage($message, 'success');
			} else {
				$message = $translator->trans("error.participation.no_meal_selected", [], 'messages');
				$this->addFlashMessage($message, 'danger');
			}
		}
		return $this->render('MealzMealBundle:Meal:guest.html.twig', array(
				'form' => $form->createView()
		));
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
}
