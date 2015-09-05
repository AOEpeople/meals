<?php

namespace Mealz\AccountingBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountingController extends BaseController {

	public function listAction() {
		if ($this->getDoorman()->isKitchenStaff()) {
			return $this->listForKitchenStaffAction();
		} elseif ($this->get('security.context')->isGranted('ROLE_USER')) {
			return $this->listForIndividualAction();
		} else {
			throw new AccessDeniedException();
		}
	}

	public function listForKitchenStaffAction() {
		/** @var ParticipantListFactory $participantListFactory */
		$participantListFactory = $this->get('mealz_accounting.participant_list_factory');

		$startDay = new \DateTime('first day of last month');
		$endDay = new \DateTime('last day of last month');

		$participantList = $participantListFactory->getList($startDay, $endDay);

		return $this->render('MealzAccountingBundle:Accounting:list_kitchen.html.twig', array(
			'startDay' => $startDay,
			'endDay' => $endDay,
			'participantList' => $participantList
		));
	}

	public function listForIndividualAction() {
		/** @var ParticipantListFactory $participantListFactory */
		$participantListFactory = $this->get('mealz_accounting.participant_list_factory');

		$startDay = new \DateTime('first day of last month');
		$endDay = new \DateTime('last day of last month');

		$profile = $this->getProfile();

		$participantList = $participantListFactory->getList($startDay, $endDay, $profile);

		return $this->render('MealzAccountingBundle:Accounting:list_individual.html.twig', array(
			'startDay' => $startDay,
			'endDay' => $endDay,
			'participations' => $participantList->getParticipations($profile),
			'countAccountableParticipations' => $participantList->countAccountableParticipations($profile)
		));
	}

}